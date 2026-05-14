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

        if ($user->esAdmin()) {
            $tickets = Ticket::with('empresa.usuario')
                ->orderByRaw("FIELD(estado, 'abierto', 'en_proceso', 'resuelto', 'cerrado')")
                ->orderBy('sla_due_at')
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
            'categoria'   => 'required|string|max:100',
            'prioridad'   => 'required|in:baja,media,alta,urgente',
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
            ->with('success', "Ticket creado. SLA: {$clasificacion['sla_minutes']} minutos ({$clasificacion['prioridad']}).");
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

        $request->validate(['mensaje' => 'required|string|max:3000']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'mensaje'   => $request->mensaje,
        ]);

        return back()->with('success', 'Respuesta enviada.');
    }

    public function cambiarEstado(Request $request, Ticket $ticket)
    {
        abort_unless(Auth::user()->esAdmin(), 403);

        $request->validate(['estado' => 'required|in:abierto,en_proceso,resuelto,cerrado']);

        $updates = ['estado' => $request->estado];
        if ($request->estado === 'resuelto') {
            $updates['resuelto_at'] = now();
        }

        $ticket->update($updates);

        return back()->with('success', 'Estado del ticket actualizado.');
    }

    private function autorizar(Ticket $ticket): void
    {
        $user = Auth::user();
        if ($user->esAdmin()) return;

        $empresa = $user->empresa;
        abort_unless($empresa && $ticket->empresa_id === $empresa->id, 403);
    }
}
