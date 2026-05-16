<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Services\SlaInteligenteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->esAdmin() || $user->esInterno()) {
            $tickets = Ticket::with('empresa.usuario')
                ->orderByRaw("CASE estado WHEN 'abierto' THEN 1 WHEN 'en_proceso' THEN 2 WHEN 'resuelto' THEN 3 ELSE 4 END")
                ->orderByRaw("CASE prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END")
                ->orderByRaw('COALESCE(sla_due_at, created_at) ASC')
                ->paginate(15);
        } else {
            $empresa = $user->empresa;
            abort_unless($empresa, 403);
            $tickets = Ticket::where('empresa_id', $empresa->id)
                ->latest()
                ->paginate(15);
        }

        return view('tickets.index', compact('tickets'));
    }

    public function crear()
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);
        return view('tickets.create');
    }

    public function guardar(Request $request, SlaInteligenteService $sla)
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);

        $data = $request->validate([
            'asunto'      => 'required|string|max:200',
            'descripcion' => 'required|string|max:3000',
            'categoria'   => 'required|in:' . implode(',', array_keys(Ticket::categorias())),
            'prioridad'   => 'required|in:' . implode(',', array_keys(Ticket::prioridades())),
        ]);

        // Calcular SLA inteligente
        $prioridadSla = $data['prioridad'] === 'urgente' ? 'alta' : $data['prioridad'];
        $clasificacion = $sla->clasificar(
            'solicitud_empresa_soporte',
            $data['asunto'],
            $data['descripcion'],
            $prioridadSla
        );

        $ticket = Ticket::create([
            'empresa_id'  => $empresa->id,
            'asunto'      => $data['asunto'],
            'descripcion' => $data['descripcion'],
            'categoria'   => $data['categoria'],
            'prioridad'   => $data['prioridad'],
            'estado'      => 'abierto',
            'sla_due_at'  => now()->addMinutes($clasificacion['sla_minutes']),
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket creado. SLA estimado: {$clasificacion['sla_minutes']} minutos.");
    }

    public function show(Ticket $ticket)
    {
        $this->autorizar($ticket);
        $ticket->load(['mensajes.user', 'empresa.usuario', 'asignado']);
        return view('tickets.show', compact('ticket'));
    }

    public function responder(Request $request, Ticket $ticket)
    {
        $this->autorizar($ticket);
        abort_if(in_array($ticket->estado, ['resuelto', 'cerrado'], true), 422, 'El ticket ya fue cerrado.');

        $request->validate(['mensaje' => 'required|string|max:3000']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'mensaje'   => $request->mensaje,
        ]);

        if ($ticket->estado === 'abierto') {
            $ticket->update(['estado' => 'en_proceso']);
        }

        return back()->with('success', 'Respuesta enviada correctamente.');
    }

    public function cambiarEstado(Request $request, Ticket $ticket)
    {
        abort_unless(Auth::user()->esAdmin() || Auth::user()->esInterno(), 403);

        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Ticket::estados())),
        ]);

        $updates = ['estado' => $request->estado];
        if (in_array($request->estado, ['resuelto', 'cerrado'], true)) {
            $updates['resuelto_at'] = now();
        }

        $ticket->update($updates);

        return back()->with('success', 'Estado del ticket actualizado correctamente.');
    }

    public function asignar(Request $request, Ticket $ticket)
    {
        abort_unless(Auth::user()->esAdmin() || Auth::user()->esInterno(), 403);

        $data = $request->validate([
            'asignado_a' => ['nullable', 'exists:users,id'],
        ]);

        $ticket->update(['asignado_a' => $data['asignado_a'] ?? null]);

        return back()->with('success', 'Ticket asignado correctamente.');
    }

    private function autorizar(Ticket $ticket): void
    {
        $user = Auth::user();
        if ($user->esAdmin() || $user->esInterno()) {
            return;
        }

        $empresa = $user->empresa;
        abort_unless($empresa && $ticket->empresa_id === $empresa->id, 403);
    }
}
