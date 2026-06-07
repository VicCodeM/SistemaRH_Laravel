<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\CatalogoServicioRecurso;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogoServicioRecursoController extends Controller
{
    public function store(Request $request, CatalogoServicio $catalogo)
    {
        $data = $this->validar($request, null);
        $archivos = $data['archivos'] ?? [];
        $ordenBase = (int) ($data['orden'] ?? ((int) ($catalogo->recursos()->max('orden') ?? 0) + 1));
        $totalArchivos = count($archivos);

        foreach ($archivos as $indice => $archivo) {
            $payload = $this->armarPayload($catalogo, null, [
                ...$data,
                'titulo' => $this->resolverTituloArchivo($data['titulo'] ?? null, $archivo, $totalArchivos, $indice),
                'orden' => $ordenBase + $indice,
            ], $archivo);

            CatalogoServicioRecurso::create([
                'catalogo_servicio_id' => $catalogo->id,
                'user_id' => auth()->id(),
                ...$payload,
            ]);
        }

        $mensaje = $totalArchivos > 1
            ? $totalArchivos . ' diapositivas agregadas correctamente.'
            : 'Diapositiva agregada correctamente.';

        return back()->with('success', $mensaje);
    }

    public function update(Request $request, CatalogoServicioRecurso $recurso)
    {
        $recurso->loadMissing('catalogoServicio');
        abort_unless($recurso->catalogoServicio, 404);

        $data = $this->validar($request, $recurso);
        $payload = $this->armarPayload($recurso->catalogoServicio, $recurso, $data, $request->file('archivo'));

        $recurso->update($payload);

        return back()->with('success', 'Diapositiva actualizada correctamente.');
    }

    public function destroy(CatalogoServicioRecurso $recurso)
    {
        $recurso->loadMissing('catalogoServicio');

        abort_unless($recurso->catalogoServicio, 404);

        if ($recurso->archivo_path && Storage::disk('public')->exists($recurso->archivo_path)) {
            Storage::disk('public')->delete($recurso->archivo_path);
        }

        $recurso->delete();

        return back()->with('success', 'Diapositiva eliminada.');
    }

    private function validar(Request $request, ?CatalogoServicioRecurso $recurso): array
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
        CatalogoServicio $catalogo,
        ?CatalogoServicioRecurso $recurso,
        array $data,
        ?UploadedFile $archivo = null
    ): array {
        $payload = [
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'orden' => $data['orden'] ?? ($recurso?->orden ?? ((int) ($catalogo->recursos()->max('orden') ?? 0) + 1)),
        ];

        if ($archivo instanceof UploadedFile) {
            if ($recurso && $recurso->archivo_path && Storage::disk('public')->exists($recurso->archivo_path)) {
                Storage::disk('public')->delete($recurso->archivo_path);
            }

            $ruta = $archivo->storePublicly('catalogos/' . $catalogo->id . '/recursos', 'public');

            $payload['archivo_path'] = $ruta;
            $payload['archivo_original'] = $archivo->getClientOriginalName();
            $payload['mime_type'] = $archivo->getClientMimeType();
            $payload['tamano_bytes'] = $archivo->getSize();
        }

        return $payload;
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
}
