<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisterEmpresaController extends Controller
{
    public function create(): View
    {
        return view('auth.register-empresa');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
            'nombre_empresa'        => ['required', 'string', 'max:200'],
            'rfc'                   => ['nullable', 'string', 'max:20'],
            'telefono'              => ['required', 'string', 'max:20'],
            'ciudad'                => ['required', 'string', 'max:100'],
            'giro_o_industria'      => ['nullable', 'string', 'max:150'],
        ]);

        $user = User::create([
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'rol'                => 'empresa',
            'estado'             => 'activo',
            'email_verified_at'  => now(),
        ]);

        Empresa::create([
            'usuario_id'    => $user->id,
            'nombre_empresa'=> $request->nombre_empresa,
            'rfc'           => $request->rfc,
            'telefono'      => $request->telefono,
            'ciudad'        => $request->ciudad,
            'descripcion'   => $request->giro_o_industria,
            'estado'        => 'pendiente',
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('empresa.dashboard')
            ->with('success', 'Registro exitoso. Tu empresa está pendiente de aprobación.');
    }
}
