<?php

namespace App\Http\Controllers\Candidato;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSistema;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CandidatoController extends Controller
{
    private function candidatoActual()
    {
        return Auth::user()?->candidato;
    }

    private function requiereAprobacionPrevia(): bool
    {
        return ConfiguracionSistema::boolean('candidato_requiere_aprobacion', false);
    }

    private function accesoBloqueado(): bool
    {
        return $this->requiereAprobacionPrevia() && Auth::user()?->estado !== 'activo';
    }

    private function requiereSolicitudAprobada(): ?RedirectResponse
    {
        if ($this->accesoBloqueado()) {
            return redirect()
                ->route('candidato.dashboard')
                ->with('error', 'Tu acceso todavía está pendiente de aprobación.');
        }

        $candidato = $this->candidatoActual();

        if (! $candidato) {
            return redirect()->route('candidato.solicitud');
        }

        if ($candidato->solicitud_estado === 'aprobada') {
            return null;
        }

        return redirect()
            ->route('candidato.dashboard')
            ->with('error', 'Primero completa y envía tu solicitud para ver las vacantes.');
    }

    public function dashboard(\App\Services\ResumenRapidoService $resumen): View
    {
        $candidato = $this->candidatoActual();

        if (! $candidato) {
            return view('candidato.pendiente', [
                'titulo' => 'Tu perfil aún no está listo',
                'mensaje' => 'Primero completa tu solicitud para comenzar a usar el panel.',
                'detalle' => 'Si ya registraste tu cuenta, entra a la solicitud y termina de capturar tu información.',
            ]);
        }

        if ($this->accesoBloqueado()) {
            return view('candidato.pendiente', [
                'titulo' => 'Tu acceso está en revisión',
                'mensaje' => 'El admin debe aprobar tu cuenta antes de que puedas completar la solicitud.',
                'detalle' => 'En cuanto te activen podrás entrar, llenar tu expediente y continuar con el proceso.',
            ]);
        }

        $postulacionesRecientes = Postulacion::with('vacante.empresa')
            ->where('candidato_id', $candidato->id)
            ->orderByDesc('fecha_postulacion')
            ->limit(4)
            ->get();

        $vacantesRecientes = $candidato->solicitud_estado === 'aprobada'
            ? Vacante::with('empresa')
                ->where('estado', 'activa')
                ->orderByDesc('fecha_publicacion')
                ->limit(4)
                ->get()
            : collect();

        $acciones = $resumen->paraCandidato($candidato);

        return view('candidato.dashboard', [
            'candidato' => $candidato,
            'postulacionesRecientes' => $postulacionesRecientes,
            'vacantesRecientes' => $vacantesRecientes,
            'acciones' => $acciones,
        ]);
    }

    public function solicitud(): View
    {
        if ($this->accesoBloqueado()) {
            return view('candidato.pendiente', [
                'titulo' => 'Tu acceso está en revisión',
                'mensaje' => 'Aún no puedes completar tu solicitud.',
                'detalle' => 'El admin debe aprobar tu acceso antes de que puedas capturar tu expediente.',
            ]);
        }

        return view('candidato.solicitud');
    }

    public function vacantes(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->requiereSolicitudAprobada()) {
            return $redirect;
        }

        $candidato = $this->candidatoActual();

        $query = Vacante::with('empresa')
            ->with(['postulaciones' => fn ($postulaciones) => $postulaciones->where('candidato_id', $candidato->id)])
            ->where('estado', 'activa')
            ->orderByDesc('fecha_publicacion');

        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->input('buscar'));

            $query->where(function ($q) use ($buscar) {
                $q->where('titulo', 'like', "%{$buscar}%")
                    ->orWhereHas('empresa', fn ($empresa) => $empresa->where('nombre_empresa', 'like', "%{$buscar}%"));
            });
        }

        $vacantes = $query->paginate(12)->withQueryString();

        return view('candidato.vacantes', compact('vacantes'));
    }

    public function vacanteModal(Vacante $vacante): View|RedirectResponse
    {
        if ($redirect = $this->requiereSolicitudAprobada()) {
            return $redirect;
        }

        $candidato = $this->candidatoActual();
        $vacante->load([
            'empresa.usuario',
            'postulaciones' => fn ($postulaciones) => $postulaciones->where('candidato_id', $candidato->id),
        ]);

        $postulacionActual = $vacante->postulaciones->first();
        $yaPostulado = (bool) $postulacionActual;

        $puedePostular = $candidato->solicitud_estado === 'aprobada'
            && $vacante->estado === 'activa'
            && ! $yaPostulado;

        return view('candidato.vacante-modal', compact('vacante', 'candidato', 'yaPostulado', 'puedePostular', 'postulacionActual'));
    }

    public function postulaciones(): View|RedirectResponse
    {
        if ($redirect = $this->requiereSolicitudAprobada()) {
            return $redirect;
        }

        $candidato = $this->candidatoActual();

        $postulaciones = Postulacion::where('candidato_id', $candidato->id)
            ->with('vacante.empresa')
            ->orderByDesc('fecha_postulacion')
            ->get();

        return view('candidato.postulaciones', compact('postulaciones'));
    }

    public function eliminarPostulacion(Postulacion $postulacion): RedirectResponse
    {
        if ($redirect = $this->requiereSolicitudAprobada()) {
            return $redirect;
        }

        $candidato = $this->candidatoActual();

        abort_if($postulacion->candidato_id !== $candidato->id, 403, 'Esta postulación no te pertenece.');
        abort_if($postulacion->estado !== 'postulado', 422, 'Solo puedes eliminar postulaciones que aún no han sido revisadas.');

        $postulacion->delete();

        return redirect()
            ->route('candidato.postulaciones')
            ->with('success', 'Postulación eliminada.');
    }

    public function postular(Vacante $vacante): RedirectResponse
    {
        if ($redirect = $this->requiereSolicitudAprobada()) {
            return $redirect;
        }

        $candidato = $this->candidatoActual();

        if ($vacante->estado !== 'activa') {
            return back()->with('error', 'Esta solicitud ya no está disponible.');
        }

        $existe = Postulacion::where('candidato_id', $candidato->id)
            ->where('vacante_id', $vacante->id)
            ->exists();

        if ($existe) {
            return back()->with('error', 'Ya enviaste una solicitud para esta vacante.');
        }

        Postulacion::create([
            'candidato_id' => $candidato->id,
            'vacante_id' => $vacante->id,
            'estado' => 'postulado',
            'fecha_postulacion' => now(),
        ]);

        return redirect()
            ->route('candidato.postulaciones')
            ->with('success', 'Solicitud enviada correctamente.');
    }
}
