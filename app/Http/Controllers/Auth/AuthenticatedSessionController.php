<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\AccesoMunicipioService;
use App\Services\SesionUnicaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly SesionUnicaService $sesionUnica,
        private readonly AccesoMunicipioService $accesoMunicipio,
    )
    {
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $usuario = $request->user();
        if (! $usuario instanceof User) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        if (! $this->accesoMunicipio->puedeAcceder($usuario)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => $this->accesoMunicipio->mensajeDenegado($usuario),
                ])
                ->onlyInput('email');
        }

        $retorno = $request->session()->pull('url.intended', route('dashboard', absolute: false));
        $sesionesActivas = $this->sesionUnica->sesionesActivasDetalle($usuario, $request->session()->getId());

        if ($sesionesActivas !== []) {
            $request->session()->put('sesion_unica', [
                'usuario_id' => $usuario->id,
                'recordarme' => $request->boolean('remember'),
                'retorno' => $retorno,
                'sesiones_detectadas' => count($sesionesActivas),
                'sesiones' => $sesionesActivas,
            ]);

            return redirect()
                ->route('sesiones.confirmar-cierre')
                ->with('warning', $this->mensajeSesionesDetectadas(count($sesionesActivas)));
        }

        return redirect()->to($retorno);
    }

    public function confirmarCierreSesiones(Request $request): RedirectResponse|View
    {
        $pendiente = $this->datosSesionUnicaPendiente($request);

        if ($pendiente === null) {
            return redirect()->route('dashboard');
        }

        return view('auth.confirmar-cierre-sesiones', [
            'sesionesDetectadas' => (int) ($pendiente['sesiones_detectadas'] ?? 0),
            'sesiones' => is_array($pendiente['sesiones'] ?? null) ? $pendiente['sesiones'] : [],
            'retorno' => $this->retornoSesionUnica($pendiente),
        ]);
    }

    public function cerrarOtrasSesiones(Request $request): RedirectResponse
    {
        $pendiente = $this->datosSesionUnicaPendiente($request);

        if ($pendiente === null) {
            return redirect()->route('dashboard');
        }

        $usuario = $request->user();
        if (! $usuario instanceof User || (int) $pendiente['usuario_id'] !== $usuario->id) {
            $request->session()->forget('sesion_unica');

            return redirect()->route('dashboard');
        }

        $recordarme = (bool) ($pendiente['recordarme'] ?? false);

        $usuario->forceFill([
            'remember_token' => Str::random(60),
        ])->saveQuietly();

        if ($recordarme) {
            Auth::guard('web')->login($usuario, true);
        }

        $sesionesCerradas = $this->sesionUnica->cerrarOtrasSesiones($usuario, $request->session()->getId());
        $retorno = $this->retornoSesionUnica($pendiente);

        $request->session()->forget('sesion_unica');

        return redirect()
            ->to($retorno)
            ->with('success', $sesionesCerradas > 0
                ? 'Se cerraron las otras sesiones activas.'
                : 'No había otras sesiones activas para cerrar.');
    }

    public function continuarSinCerrarSesiones(Request $request): RedirectResponse
    {
        $pendiente = $this->datosSesionUnicaPendiente($request);

        if ($pendiente === null) {
            return redirect()->route('dashboard');
        }

        $retorno = $this->retornoSesionUnica($pendiente);
        $request->session()->forget('sesion_unica');

        return redirect()
            ->to($retorno)
            ->with('warning', 'Se mantuvieron activas las demás sesiones de tu cuenta.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
        $request->session()->forget('sesion_unica');

        return redirect('/');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function datosSesionUnicaPendiente(Request $request): ?array
    {
        $pendiente = $request->session()->get('sesion_unica');

        if (! is_array($pendiente) || ! $request->user() || (int) ($pendiente['usuario_id'] ?? 0) !== $request->user()->id) {
            return null;
        }

        return $pendiente;
    }

    /**
     * @param array<string, mixed> $pendiente
     */
    private function retornoSesionUnica(array $pendiente): string
    {
        return (string) ($pendiente['retorno'] ?? route('dashboard', absolute: false));
    }

    private function mensajeSesionesDetectadas(int $sesionesActivas): string
    {
        if ($sesionesActivas === 1) {
            return 'Detectamos 1 sesión activa en otro dispositivo. Puedes cerrarla antes de continuar.';
        }

        return "Detectamos {$sesionesActivas} sesiones activas en otros dispositivos. Puedes cerrarlas antes de continuar.";
    }
}
