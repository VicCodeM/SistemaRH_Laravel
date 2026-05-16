<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Empresa;
use App\Models\Ticket;
use App\Models\Vacante;
use Illuminate\Support\Collection;

/**
 * Servicio para búsquedas transversales en el sistema.
 */
class BusquedaService
{
    /**
     * Busca en empresas, candidatos, vacantes y tickets por un término común.
     *
     * @return Collection<int, array{tipo: string, titulo: string, sub: string, url: string, estado: string}>
     */
    public function global(string $q): Collection
    {
        $like = "%{$q}%";

        $empresas = $this->buscarEmpresas($like);
        $candidatos = $this->buscarCandidatos($like);
        $vacantes = $this->buscarVacantes($like);
        $tickets = $this->buscarTickets($like);

        return $empresas->merge($candidatos)->merge($vacantes)->merge($tickets);
    }

    private function buscarEmpresas(string $like): Collection
    {
        return Empresa::where('nombre_empresa', 'like', $like)
            ->orWhere('rfc', 'like', $like)
            ->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', $like))
            ->with('usuario')
            ->limit(8)
            ->get()
            ->map(fn ($e) => [
                'tipo' => 'empresa',
                'titulo' => $e->nombre_empresa,
                'sub' => $e->rfc ?? 'Sin RFC',
                'url' => route('admin.empresas'),
                'estado' => $e->estado,
            ]);
    }

    private function buscarCandidatos(string $like): Collection
    {
        return Candidato::where('nombre', 'like', $like)
            ->orWhere('apellido_paterno', 'like', $like)
            ->orWhere('apellido_materno', 'like', $like)
            ->orWhere('curp', 'like', $like)
            ->orWhereHas('usuario', fn ($u) => $u->where('email', 'like', $like))
            ->with('usuario')
            ->limit(8)
            ->get()
            ->map(fn ($c) => [
                'tipo' => 'candidato',
                'titulo' => $c->nombreCompleto(),
                'sub' => $c->puesto_deseado ?? 'Sin puesto',
                'url' => route('admin.candidatos'),
                'estado' => $c->solicitud_estado,
            ]);
    }

    private function buscarVacantes(string $like): Collection
    {
        return Vacante::where('titulo', 'like', $like)
            ->orWhere('descripcion', 'like', $like)
            ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', $like))
            ->with('empresa')
            ->limit(8)
            ->get()
            ->map(fn ($v) => [
                'tipo' => 'vacante',
                'titulo' => $v->titulo,
                'sub' => $v->empresa?->nombre_empresa ?? 'Sin empresa',
                'url' => route('admin.vacantes'),
                'estado' => $v->estado,
            ]);
    }

    private function buscarTickets(string $like): Collection
    {
        return Ticket::where('asunto', 'like', $like)
            ->orWhere('descripcion', 'like', $like)
            ->orWhereHas('empresa', fn ($e) => $e->where('nombre_empresa', 'like', $like))
            ->with('empresa')
            ->limit(8)
            ->get()
            ->map(fn ($t) => [
                'tipo' => 'ticket',
                'titulo' => $t->asunto,
                'sub' => $t->empresa?->nombre_empresa ?? 'Sin empresa',
                'url' => route('tickets.show', $t),
                'estado' => $t->estado,
            ]);
    }
}
