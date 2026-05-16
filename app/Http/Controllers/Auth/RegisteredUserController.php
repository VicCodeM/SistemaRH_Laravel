<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Candidato;
use App\Models\ConfiguracionSistema;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'name.required' => 'Escribe tu nombre completo.',
                'email.required' => 'Escribe tu correo electrónico.',
                'email.unique' => 'Ya existe una cuenta con ese correo. Usa recuperación de acceso.',
                'password.required' => 'Define una contraseña.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]
        );

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
                    'email_verified_at' => $estadoUsuario === 'activo' ? now() : null,
                ]);

                Candidato::create([
                    'usuario_id' => $user->id,
                    'nombre' => trim($validated['name']),
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
