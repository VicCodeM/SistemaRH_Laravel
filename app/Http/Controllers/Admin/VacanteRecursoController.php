<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vacante;
use App\Models\VacanteRecurso;
use App\Services\ImagenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VacanteRecursoController extends Controller
{
    public function store(Request $request, Vacante $vacante)
    {
        $data = $this->validar($request, null);
        $archivos = $data['archivos'] ?? [];
        $ordenBase = (int) ($data['orden'] ?? ((int) ($vacante->recursos()->max('orden') ?? 0) + 1));
        $totalArchivos = count($archivos);

        foreach ($archivos as $indice => $archivo) {
            $payload = $this->armarPayload($vacante, null, [
                ...$data,
                'titulo' => $this->resolverTituloArchivo($data['titulo'] ?? null, $archivo, $totalArchivos, $indice),
                'orden' => $ordenBase + $indice,
            ], $archivo);

            VacanteRecurso::create([
                'vacante_id' => $vacante->id,
                'user_id' => auth()->id(),
                ...$payload,
            ]);
        }

        $this->activarPresentacion($vacante);

        $mensaje = $totalArchivos > 1
            ? $totalArchivos . ' diapositivas agregadas correctamente.'
            : 'Diapositiva agregada correctamente.';

        return $this->redirigirAlEditor($vacante, $mensaje);
    }

    public function update(Request $request, VacanteRecurso $recurso)
    {
        $recurso->loadMissing('vacante');
        abort_unless($recurso->vacante, 404);

        $data = $this->validar($request, $recurso);
        $payload = $this->armarPayload($recurso->vacante, $recurso, $data, $request->file('archivo'));

        $recurso->update($payload);
        $this->activarPresentacion($recurso->vacante);

        return $this->redirigirAlEditor($recurso->vacante, 'Diapositiva actualizada correctamente.');
    }

    public function reordenar(Request $request, Vacante $vacante)
    {
        $data = $request->validate([
            'orden' => ['required', 'array'],
            'orden.*' => ['integer', 'exists:vacante_recursos,id'],
        ]);

        foreach ($data['orden'] as $posicion => $id) {
            VacanteRecurso::where('id', $id)
                ->where('vacante_id', $vacante->id)
                ->update(['orden' => $posicion + 1]);
        }

        return response()->json(['ok' => true]);
    }

    public function updateInline(Request $request, VacanteRecurso $recurso)
    {
        $recurso->loadMissing('vacante');
        abort_unless($recurso->vacante, 404);

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:140'],
            'descripcion' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ]);

        $recurso->update($data);

        return response()->json(['ok' => true, 'titulo' => $recurso->titulo]);
    }

    public function destroy(VacanteRecurso $recurso)
    {
        $recurso->loadMissing('vacante');
        abort_unless($recurso->vacante, 404);

        $this->eliminarArchivosAnteriores($recurso);

        $recurso->delete();

        return $this->redirigirAlEditor($recurso->vacante, 'Diapositiva eliminada.');
    }

    private function validar(Request $request, ?VacanteRecurso $recurso): array
    {
        if (! $recurso && $request->hasFile('archivo') && ! $request->hasFile('archivos')) {
            $request->files->set('archivos', [$request->file('archivo')]);
        }

        $reglas = [
            'titulo' => array_filter([
                $recurso ? 'required' : 'nullable',
                'string',
                'max:140',
            ]),
            'tipo' => ['required', 'in:presentacion'],
            'modo_carga' => ['required', 'in:archivo'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];

        if ($recurso) {
            $reglas['archivo'] = array_filter([
                'nullable',
                'file',
                'max:25600',
                'mimes:jpg,jpeg,png,webp,gif',
            ]);
        } else {
            $reglas['archivos'] = ['required', 'array', 'min:1', 'max:20'];
            $reglas['archivos.*'] = ['file', 'max:25600', 'mimes:jpg,jpeg,png,webp,gif'];
        }

        $data = $request->validate($reglas);

        if (! $recurso) {
            $data['archivos'] = array_values(array_filter(
                $request->file('archivos', []),
                fn ($archivo) => $archivo instanceof UploadedFile
            ));
        }

        return $data;
    }

    private function armarPayload(
        Vacante $vacante,
        ?VacanteRecurso $recurso,
        array $data,
        ?UploadedFile $archivo = null
    ): array {
        $payload = [
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'orden' => $data['orden'] ?? ($recurso?->orden ?? ((int) ($vacante->recursos()->max('orden') ?? 0) + 1)),
        ];

        if ($archivo instanceof UploadedFile) {
            $this->eliminarArchivosAnteriores($recurso);

            $carpeta = 'vacantes/' . $vacante->id . '/recursos';
            $imagenService = app(ImagenService::class);
            $resultado = $imagenService->procesar($archivo, $carpeta);

            $payload['archivo_path'] = $resultado['path'];
            $payload['thumb_path'] = $resultado['thumb_path'];
            $payload['archivo_original'] = $archivo->getClientOriginalName();
            $payload['mime_type'] = $resultado['mime_type'] ?? $archivo->getClientMimeType();
            $payload['tamano_bytes'] = $resultado['tamano'];
        }

        return $payload;
    }

    private function eliminarArchivosAnteriores(?VacanteRecurso $recurso): void
    {
        if (! $recurso) {
            return;
        }

        if ($recurso->archivo_path && Storage::disk('public')->exists($recurso->archivo_path)) {
            Storage::disk('public')->delete($recurso->archivo_path);
        }
        if ($recurso->thumb_path && Storage::disk('public')->exists($recurso->thumb_path)) {
            Storage::disk('public')->delete($recurso->thumb_path);
        }
    }

    private function resolverTituloArchivo(?string $tituloBase, UploadedFile $archivo, int $totalArchivos, int $indice): string
    {
        $tituloBase = trim((string) $tituloBase);

        if ($tituloBase !== '') {
            return $totalArchivos > 1
                ? $tituloBase . ' ' . ($indice + 1)
                : $tituloBase;
        }

        $nombreOriginal = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);

        return Str::of($nombreOriginal)
            ->replace(['_', '-'], ' ')
            ->squish()
            ->title()
            ->value();
    }

    private function redirigirAlEditor(Vacante $vacante, string $mensaje): RedirectResponse
    {
        return redirect()
            ->to(route('admin.vacantes.editar', $vacante) . '#presentacion-vacante')
            ->with('success', $mensaje);
    }

    private function activarPresentacion(Vacante $vacante): void
    {
        if ($vacante->presentacion_activa) {
            return;
        }

        $vacante->forceFill(['presentacion_activa' => true])->save();
    }
}
