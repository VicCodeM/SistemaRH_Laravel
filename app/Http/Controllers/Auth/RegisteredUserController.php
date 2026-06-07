<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\ConfiguracionSistema;
use App\Models\User;
use App\Services\AccesoMunicipioService;
use App\Services\SesionUnicaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly SesionUnicaService $sesionUnica,
        private readonly AccesoMunicipioService $accesoMunicipio,
    ) {
    }

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $requiereVerificacion = User::requireEmailVerification();
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
                'municipio' => ['required', 'string', 'max:150'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'acepta_terminos' => ['accepted'],
            ],
            [
                'name.required' => 'Escribe tu nombre completo.',
                'email.required' => 'Escribe tu correo electrónico.',
                'email.unique' => 'Ya existe una cuenta con ese correo. Usa recuperación de acceso.',
                'municipio.required' => 'Escribe tu municipio de residencia.',
                'password.required' => 'Define una contraseña.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'acepta_terminos.accepted' => 'Debes aceptar los Términos del servicio y la Política de privacidad.',
            ]
        );

        if (! $this->accesoMunicipio->municipioPermitido($validated['municipio'])) {
            return back()
                ->withErrors(['municipio' => 'Tu municipio no está autorizado para registrarse en este sistema.'])
                ->withInput();
        }

        $requiereAprobacion = ConfiguracionSistema::boolean('candidato_requiere_aprobacion', false);
        $estadoUsuario = $requiereAprobacion ? 'pendiente' : 'activo';

        try {
            $user = DB::transaction(function () use ($validated, $estadoUsuario) {
                $user = User::create([
                    'name' => trim($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'password' => Hash::make($validated['password']),
                    'rol' => 'candidato',
                    'estado' => $estadoUsuario,
                    'email_verified_at' => User::emailVerifiedAtInitial(),
                ]);

                Candidato::create([
                    'usuario_id' => $user->id,
                    'nombre' => trim($validated['name']),
                    'municipio' => trim($validated['municipio']),
                    'solicitud_estado' => 'borrador',
                ]);

                return $user;
            });
        } catch (UniqueConstraintViolationException $e) {
            if (str_contains($e->getMessage(), 'users.users_email_unique')) {
                return back()
                    ->withErrors(['email' => 'Ya existe una cuenta con ese correo. Usa recuperación de acceso.'])
                    ->withInput();
            }

            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'users.users_email_unique')) {
                return back()
                    ->withErrors(['email' => 'Ya existe una cuenta con ese correo. Usa recuperación de acceso.'])
                    ->withInput();
            }

            throw $e;
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        $this->sesionUnica->cerrarOtrasSesiones($user, $request->session()->getId());

        if ($requiereVerificacion) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()
                ->route('verification.notice')
                ->with('status', 'Te enviamos un correo para verificar tu cuenta.');
        }

        if ($requiereAprobacion) {
            return redirect()
                ->route('candidato.dashboard')
                ->with('error', 'Tu acceso quedó pendiente de aprobación. En cuanto el admin lo active, podrás completar tu solicitud.');
        }

        return redirect()
            ->route('candidato.solicitud')
            ->with('success', 'Registro completado. Ahora completa tu solicitud.');
    }
}
