<?php

namespace App\Http\Controllers;

use App\Models\ComentarioServicio;
use App\Models\ServicioAsignado;
use Illuminate\Http\Request;

/**
 * Comentarios/actualizaciones en un pedido de servicio.
 * Pueden escribir: admin, interno asignado, solicitante (empresa o candidato dueño).
 */
class ComentarioServicioController extends Controller
{
    public function store(Request $request, ServicioAsignado $servicio)
    {
        $this->autorizarParaEscribir($servicio);

        $data = $request->validate([
            'mensaje' => ['required', 'string', 'max:2000'],
        ]);

        ComentarioServicio::create([
            'servicio_asignado_id' => $servicio->id,
            'user_id'              => auth()->id(),
            'mensaje'              => $data['mensaje'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroyModal(ComentarioServicio $comentario)
    {
        $this->autorizarParaEliminar($comentario);

        $comentario->loadMissing(['autor', 'servicio.servicio']);

        $config = [
            'titulo' => 'Eliminar comentario',
            'descripcion' => 'Se borrara este comentario del historial del pedido.',
            'mensaje' => 'Confirma si deseas eliminar este comentario. Esta accion no se puede deshacer.',
            'ruta' => route('pedidos.comentarios.destroy', $comentario),
            'metodo' => 'DELETE',
            'boton' => 'Eliminar comentario',
            'clase' => 'btn-danger',
        ];

        $registro = [
            'titulo' => $comentario->autor?->name ?? 'Comentario',
            'detalle' => ($comentario->servicio?->servicio?->nombre ?? 'Pedido de servicio') . ' · ' . \Illuminate\Support\Str::limit($comentario->mensaje, 90),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function destroy(ComentarioServicio $comentario)
    {
        $this->autorizarParaEliminar($comentario);

        $comentario->delete();

        return back()->with('success', 'Comentario eliminado.');
    }

    private function autorizarParaEscribir(ServicioAsignado $servicio): void
    {
        $user = auth()->user();
        $rol  = $user?->rol;

        // Admin: siempre
        if ($rol === 'admin') return;

        // Interno: solo si es el asignado
        if ($rol === 'interno' && $servicio->asignado_a === $user->id) return;

        // Empresa: solo si es la solicitante
        if ($rol === 'empresa'
            && $servicio->asignable_type === \App\Models\Empresa::class
            && $servicio->asignable_id === $user->empresa?->id) return;

        // Candidato: solo si es el solicitante
        if ($rol === 'candidato'
            && $servicio->asignable_type === \App\Models\Candidato::class
            && $servicio->asignable_id === $user->candidato?->id) return;

        abort(403, 'No puedes comentar en este pedido.');
    }

    private function autorizarParaEliminar(ComentarioServicio $comentario): void
    {
        abort_unless(
            $comentario->user_id === auth()->id() || auth()->user()?->rol === 'admin',
            403,
            'Solo el autor o admin pueden borrar este comentario.'
        );
    }
}
