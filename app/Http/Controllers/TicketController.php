<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\SlaInteligenteService;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(TicketService $ticketService)
    {
        $tickets = $ticketService->listarParaUsuario(Auth::user());

        return view('tickets.index', compact('tickets'));
    }

    public function crear()
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);
        return view('tickets.create');
    }

    public function guardar(Request $request, SlaInteligenteService $sla, TicketService $ticketService)
    {
        $empresa = Auth::user()->empresa;
        abort_unless($empresa, 403);

        $data = $request->validate([
            'asunto'      => 'required|string|max:200',
            'descripcion' => 'required|string|max:3000',
            'categoria'   => 'required|in:' . implode(',', array_keys(Ticket::categorias())),
            'prioridad'   => 'required|in:' . implode(',', array_keys(Ticket::prioridades())),
        ]);

        $ticket = $ticketService->crearConSla($data, $empresa, $sla);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', "Ticket creado. SLA estimado: {$ticket->sla_due_at->diffInMinutes(now())} minutos.");
    }

    public function show(Ticket $ticket)
    {
        $this->autorizar($ticket);
        $ticket->load(['mensajes.user', 'empresa.usuario', 'asignado']);
        return view('tickets.show', compact('ticket'));
    }

    public function responder(Request $request, Ticket $ticket, TicketService $ticketService)
    {
        $this->autorizar($ticket);

        $request->validate(['mensaje' => 'required|string|max:3000']);

        $ticketService->responder($ticket, $request->mensaje, Auth::user());

        return back()->with('success', 'Respuesta enviada correctamente.');
    }

    public function cambiarEstado(Request $request, Ticket $ticket, TicketService $ticketService)
    {
        abort_unless(Auth::user()->esAdmin() || Auth::user()->esInterno(), 403);

        $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Ticket::estados())),
        ]);

        $ticketService->cambiarEstado($ticket, $request->estado);

        return back()->with('success', 'Estado del ticket actualizado correctamente.');
    }

    public function asignar(Request $request, Ticket $ticket, TicketService $ticketService)
    {
        abort_unless(Auth::user()->esAdmin() || Auth::user()->esInterno(), 403);

        $data = $request->validate([
            'asignado_a' => ['nullable', 'exists:users,id'],
        ]);

        $ticketService->asignar($ticket, $data['asignado_a'] ?? null);

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
