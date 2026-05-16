<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Servicio para centralizar la lógica de tickets:
 * listados, creación con SLA, respuestas, transiciones de estado y asignación.
 */
class TicketService
{
    /**
     * Lista tickets según el rol del usuario.
     * Admin/Interno ven cola operativa ordenada por estado, prioridad y SLA.
     * Empresa ve solo sus propios tickets.
     */
    public function listarParaUsuario(User $user): LengthAwarePaginator
    {
        if ($user->esAdmin() || $user->esInterno()) {
            return Ticket::with('empresa.usuario')
                ->orderByRaw("CASE estado WHEN 'abierto' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'resuelto' THEN 3 ELSE 4 END")
                ->orderByRaw("CASE prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
                ->orderByRaw('COALESCE(sla_due_at, created_at) ASC')
                ->paginate(15);
        }

        $empresa = $user->empresa;
        abort_unless($empresa, 403);

        return Ticket::where('empresa_id', $empresa->id)
            ->latest()
            ->paginate(15);
    }

    /**
     * Crea un ticket calculando su SLA automáticamente.
     */
    public function crearConSla(array $input, Empresa $empresa, SlaInteligenteService $sla): Ticket
    {
        $prioridadSla = $input['prioridad'] === 'urgente' ? 'alta' : $input['prioridad'];

        $clasificacion = $sla->clasificar(
            'solicitud_empresa_soporte',
            $input['asunto'],
            $input['descripcion'],
            $prioridadSla
        );

        return Ticket::create([
            'empresa_id'  => $empresa->id,
            'asunto'      => $input['asunto'],
            'descripcion' => $input['descripcion'],
            'categoria'   => $input['categoria'],
            'prioridad'   => $input['prioridad'],
            'estado'      => 'abierto',
            'sla_due_at'  => now()->addMinutes($clasificacion['sla_minutes']),
        ]);
    }

    /**
     * Agrega una respuesta a un ticket.
     * Si el ticket está abierto, lo pasa automáticamente a "en_proceso".
     */
    public function responder(Ticket $ticket, string $mensaje, User $user): TicketMessage
    {
        abort_if(in_array($ticket->estado, ['resuelto', 'cerrado'], true), 422, 'El ticket ya fue cerrado.');

        $ticketMessage = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'mensaje'   => $mensaje,
        ]);

        if ($ticket->estado === 'abierto') {
            $ticket->update(['estado' => 'en_proceso']);
        }

        return $ticketMessage;
    }

    /**
     * Cambia el estado de un ticket aplicando reglas de negocio:
     * resuelto/cerrado marcan el timestamp de cierre.
     */
    public function cambiarEstado(Ticket $ticket, string $nuevoEstado): void
    {
        $updates = ['estado' => $nuevoEstado];

        if (in_array($nuevoEstado, ['resuelto', 'cerrado'], true)) {
            $updates['resuelto_at'] = now();
        }

        $ticket->update($updates);
    }

    /**
     * Asigna o desasigna un ticket a un usuario.
     */
    public function asignar(Ticket $ticket, ?int $usuarioId): void
    {
        $ticket->update(['asignado_a' => $usuarioId]);
    }
}
