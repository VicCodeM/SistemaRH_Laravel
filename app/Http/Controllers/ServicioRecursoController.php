<?php

namespace App\Http\Controllers;

use App\Models\ServicioAsignado;
use App\Models\ServicioRecurso;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ServicioRecursoController extends Controller
{
    public function store(Request $request, ServicioAsignado $servicio)
    {
        $this->autorizarGestion($servicio);

        $tipo = (string) $request->input('tipo', 'archivo');

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:140'],
            'tipo' => ['required', 'in:archivo,presentacion'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'archivo' => array_filter([
                'required',
                'file',
                'max:25600',
                $tipo === 'presentacion'
                    ? 'mimes:pdf,jpg,jpeg,png,webp,gif'
                    : 'mimes:pdf,jpg,jpeg,png,webp,gif,txt,md,csv,doc,docx,ppt,pptx',
            ]),
        ]);

        $archivo = $request->file('archivo');
        abort_unless($archivo instanceof UploadedFile, 422, 'Selecciona un archivo valido.');

        $ruta = $archivo->storePublicly('servicios/' . $servicio->id . '/recursos', 'public');

        ServicioRecurso::create([
            'servicio_asignado_id' => $servicio->id,
            'user_id' => auth()->id(),
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'descripcion' => $data['descripcion'] ?? null,
            'archivo_path' => $ruta,
            'archivo_original' => $archivo->getClientOriginalName(),
            'mime_type' => $archivo->getClientMimeType(),
            'tamano_bytes' => $archivo->getSize(),
            'orden' => (int) ($servicio->recursos()->max('orden') ?? 0) + 1,
        ]);

        return back()->with('success', 'Archivo agregado correctamente.');
    }

    public function destroy(ServicioRecurso $recurso)
    {
        $recurso->loadMissing('servicio');

        abort_unless($recurso->servicio, 404);
        $this->autorizarGestion($recurso->servicio);

        if ($recurso->archivo_path && Storage::disk('public')->exists($recurso->archivo_path)) {
            Storage::disk('public')->delete($recurso->archivo_path);
        }

        $recurso->delete();

        return back()->with('success', 'Archivo eliminado.');
    }

    private function autorizarGestion(ServicioAsignado $servicio): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        abort_unless($user->rol === 'admin', 403, 'Solo el administrador puede administrar archivos en este pedido.');
    }
}
