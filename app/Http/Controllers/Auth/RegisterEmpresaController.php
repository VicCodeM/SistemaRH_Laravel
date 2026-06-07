<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use App\Services\AccesoMunicipioService;
use App\Services\SesionUnicaService;
use App\Services\WorkflowService;
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

class RegisterEmpresaController extends Controller
{
    private const SESSION_KEY = 'register.empresa';

    public function __construct(
        private readonly WorkflowService $workflow,
        private readonly SesionUnicaService $sesionUnica,
        private readonly AccesoMunicipioService $accesoMunicipio,
    ) {
    }

    public function create(Request $request): View
    {
        $step = (int) $request->query('step', session(self::SESSION_KEY . '.step', 1));
        $step = max(1, min(3, $step));

        session([self::SESSION_KEY . '.step' => $step]);

        return view('auth.register-empresa', [
            'step' => $step,
            'wizardData' => session(self::SESSION_KEY . '.data', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $requiereVerificacion = User::requireEmailVerification();
        $step = max(1, min(3, (int) $request->input('step', 1)));
        $wizardData = session(self::SESSION_KEY . '.data', []);

        if ($step > 1 && (! isset($wizardData['name'], $wizardData['email'], $wizardData['password']))) {
            return redirect()->route('register.empresa', ['step' => 1])
                ->with('error', 'Primero completa el acceso para continuar con el registro.');
        }

        if ($step === 1) {
            $validated = $request->validate(
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
                    'password' => ['required', 'confirmed', Rules\Password::defaults()],
                ],
                [
                    'name.required' => 'Escribe el nombre del responsable.',
                    'email.required' => 'Escribe el correo de acceso.',
                    'email.unique' => 'Ya existe una cuenta con ese correo. Usa recuperación de acceso.',
                    'password.required' => 'Define una contraseña.',
                    'password.confirmed' => 'Las contraseñas no coinciden.',
                ]
            );

            session([
                self::SESSION_KEY . '.step' => 2,
                self::SESSION_KEY . '.data' => array_merge($wizardData, [
                    'name' => trim($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'password' => $validated['password'],
                ]),
            ]);

            return redirect()->route('register.empresa', ['step' => 2])
                ->with('status', 'Paso 1 completado. Ahora captura los datos de la empresa.');
        }

        if ($step === 2) {
            $validated = $request->validate(
                [
                    'nombre_empresa' => ['required', 'string', 'max:200'],
                    'razon_social' => ['nullable', 'string', 'max:200'],
                    'rfc' => ['nullable', 'string', 'max:20', Rule::unique('empresas', 'rfc')],
                    'giro_o_industria' => ['nullable', 'string', 'max:150'],
                ],
                [
                    'nombre_empresa.required' => 'Escribe el nombre comercial o de la empresa.',
                    'rfc.unique' => 'Ya existe una empresa registrada con ese RFC.',
                ]
            );

            session([
                self::SESSION_KEY . '.step' => 3,
                self::SESSION_KEY . '.data' => array_merge($wizardData, [
                    'nombre_empresa' => trim($validated['nombre_empresa']),
                    'razon_social' => trim((string) ($validated['razon_social'] ?? '')),
                    'rfc' => strtoupper(trim((string) ($validated['rfc'] ?? ''))),
                    'giro_o_industria' => trim((string) ($validated['giro_o_industria'] ?? '')),
                ]),
            ]);

            return redirect()->route('register.empresa', ['step' => 3])
                ->with('status', 'Paso 2 guardado. Completa el contacto y la ubicación.');
        }

        $validated = $request->validate(
            [
                'telefono' => ['required', 'string', 'max:20'],
                'direccion' => ['required', 'string', 'max:255'],
                'ciudad' => ['required', 'string', 'max:100'],
                'municipio' => ['required', 'string', 'max:150'],
                'codigo_postal' => ['nullable', 'string', 'max:10'],
                'acepta_terminos' => ['accepted'],
            ],
            [
                'telefono.required' => 'Escribe un teléfono de contacto.',
                'direccion.required' => 'Escribe la dirección completa de la empresa.',
                'ciudad.required' => 'Escribe la ciudad de la empresa.',
                'municipio.required' => 'Escribe el municipio de la empresa.',
                'acepta_terminos.accepted' => 'Debes aceptar los Términos del servicio y la Política de privacidad.',
            ]
        );

        // No persistimos la aceptación en sesión (solo se valida en este paso).
        unset($validated['acepta_terminos']);

        $wizardData = array_merge($wizardData, $validated);
        $email = strtolower(trim((string) ($wizardData['email'] ?? '')));
        $rfc = strtoupper(trim((string) ($wizardData['rfc'] ?? '')));

        if ($email !== '' && User::where('email', $email)->exists()) {
            return redirect()->route('register.empresa', ['step' => 1])
                ->with('error', 'Ya existe una cuenta con ese correo. Inicia sesión o usa recuperación de acceso.');
        }

        if ($rfc !== '' && Empresa::where('rfc', $rfc)->exists()) {
            return redirect()->route('register.empresa', ['step' => 2])
                ->with('error', 'Ya existe una empresa registrada con ese RFC.');
        }

        if (! $this->accesoMunicipio->municipioPermitido((string) $validated['municipio'])) {
            return redirect()->route('register.empresa', ['step' => 3])
                ->with('error', 'Tu municipio no está autorizado para registrar la empresa.')
                ->withInput();
        }

        try {
            $user = DB::transaction(function () use ($wizardData, $email, $rfc, $validated) {
                $user = User::create([
                    'name' => trim((string) $wizardData['name']),
                    'email' => $email,
                    'password' => Hash::make((string) $wizardData['password']),
                    'rol' => 'empresa',
                    'estado' => 'activo',
                    'email_verified_at' => User::emailVerifiedAtInitial(),
                ]);

                $empresa = Empresa::create([
                    'usuario_id' => $user->id,
                    'nombre_empresa' => trim((string) ($wizardData['nombre_empresa'] ?? '')),
                    'razon_social' => trim((string) ($wizardData['razon_social'] ?? '')) ?: null,
                    'nombre_rh' => trim((string) $wizardData['name']),
                    'rfc' => $rfc ?: null,
                    'telefono' => trim((string) ($wizardData['telefono'] ?? '')),
                    'direccion' => trim($validated['direccion']),
                    'ciudad' => trim((string) ($wizardData['ciudad'] ?? '')),
                    'municipio' => trim((string) ($wizardData['municipio'] ?? '')) ?: null,
                    'codigo_postal' => trim((string) ($wizardData['codigo_postal'] ?? '')) ?: null,
                    'descripcion' => trim((string) ($wizardData['giro_o_industria'] ?? '')) ?: null,
                    'estado' => 'pendiente',
                ]);

                $this->workflow->decideEmpresaRegistration($empresa);

                return $user;
            });
        } catch (UniqueConstraintViolationException $e) {
            if (str_contains($e->getMessage(), 'users.users_email_unique')) {
                return redirect()->route('register.empresa', ['step' => 1])
                    ->with('error', 'Ya existe una cuenta con ese correo. Inicia sesión o usa recuperación de acceso.');
            }

            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'users.users_email_unique')) {
                return redirect()->route('register.empresa', ['step' => 1])
                    ->with('error', 'Ya existe una cuenta con ese correo. Inicia sesión o usa recuperación de acceso.');
            }

            throw $e;
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();
        $this->sesionUnica->cerrarOtrasSesiones($user, $request->session()->getId());

        if ($requiereVerificacion) {
            session()->forget(self::SESSION_KEY);

            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                report($e);
            }

            return redirect()
                ->route('verification.notice')
                ->with('status', 'Te enviamos un correo para verificar tu cuenta.');
        }

        session()->forget(self::SESSION_KEY);

        return redirect()->route('empresa.dashboard')
            ->with('success', 'Registro completado. Tu empresa quedó pendiente de aprobación.');
    }
}
