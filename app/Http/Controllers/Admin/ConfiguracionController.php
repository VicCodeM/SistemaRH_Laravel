<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bitacora;
use App\Models\ConfiguracionSistema;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ConfiguracionController extends Controller
{
    public function index(Request $request)
    {
        $tabActivo = $request->string('tab')->toString() ?: 'usuarios';

        $query = User::query();

        if ($request->filled('rol')) {
            $query->where('rol', $request->rol);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'like', "%{$buscar}%")
                    ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $usuarios = $query
            ->orderByRaw("CASE rol WHEN 'admin' THEN 1 WHEN 'interno' THEN 2 WHEN 'empresa' THEN 3 WHEN 'candidato' THEN 4 ELSE 5 END")
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $bitacoras = Bitacora::with('usuario')
            ->latest()
            ->limit(20)
            ->get();

        $parametros = [
            'candidato_requiere_aprobacion' => ConfiguracionSistema::boolean('candidato_requiere_aprobacion', false),
        ];

        $stats = [
            'total' => User::count(),
            'activos' => User::where('estado', 'activo')->count(),
            'bloqueados' => User::where('estado', 'bloqueado')->count(),
            'internos' => User::where('rol', 'interno')->count(),
            'empresas' => User::where('rol', 'empresa')->count(),
            'candidatos' => User::where('rol', 'candidato')->count(),
        ];

        return view('admin.configuracion.index', compact('tabActivo', 'usuarios', 'bitacoras', 'stats', 'parametros'));
    }

    public function guardarParametros(Request $request, BitacoraService $bitacora): RedirectResponse
    {
        $request->validate([
            'candidato_requiere_aprobacion' => ['nullable', 'boolean'],
        ]);

        $requiereAprobacion = $request->boolean('candidato_requiere_aprobacion');

        ConfiguracionSistema::guardar(
            'candidato_requiere_aprobacion',
            $requiereAprobacion,
            [
                'grupo' => 'accesos',
                'tipo' => 'boolean',
                'descripcion' => 'Si se activa, cada candidato debe recibir aprobación antes de completar su solicitud.',
                'orden' => 10,
            ]
        );

        $bitacora->registrar(
            'configuracion',
            'actualizar',
            'Se actualizó el parámetro de aprobación previa de candidatos: ' . ($requiereAprobacion ? 'activado' : 'desactivado') . '.'
        );

        return back()->with('success', 'Parámetros actualizados correctamente.');
    }

    public function usuarioModal(User $usuario)
    {
        return view('admin.configuracion.usuario-modal', [
            'usuario' => $usuario->load(['candidato', 'empresa']),
            'esNuevo' => false,
        ]);
    }

    public function nuevoUsuarioModal()
    {
        return view('admin.configuracion.usuario-modal', [
            'usuario' => new User([
                'rol' => 'interno',
                'estado' => 'activo',
            ]),
            'esNuevo' => true,
        ]);
    }

    public function accionUsuarioModal(User $usuario, string $accion)
    {
        $config = match ($accion) {
            'bloquear' => [
                'titulo' => 'Bloquear usuario',
                'descripcion' => 'El usuario conservara su cuenta, pero ya no podra entrar al sistema.',
                'mensaje' => 'Confirma si deseas bloquear este acceso.',
                'ruta' => route('admin.configuracion.usuarios.estado', $usuario),
                'metodo' => 'PATCH',
                'boton' => 'Bloquear usuario',
                'clase' => 'btn-danger',
                'permitido' => auth()->id() !== $usuario->id,
                'aviso' => auth()->id() === $usuario->id ? 'No puedes bloquear tu propio acceso.' : null,
            ],
            'desbloquear' => [
                'titulo' => 'Desbloquear usuario',
                'descripcion' => 'El usuario recuperara acceso al sistema.',
                'mensaje' => 'Confirma si deseas desbloquear esta cuenta.',
                'ruta' => route('admin.configuracion.usuarios.estado', $usuario),
                'metodo' => 'PATCH',
                'boton' => 'Desbloquear usuario',
                'clase' => 'btn-success',
            ],
            'recuperar' => [
                'titulo' => 'Reenviar acceso',
                'descripcion' => 'Se generara un nuevo enlace para restablecer la contrasena.',
                'mensaje' => 'Confirma si deseas generar un nuevo enlace de acceso para este usuario.',
                'ruta' => route('admin.configuracion.usuarios.recuperar', $usuario),
                'metodo' => 'POST',
                'boton' => 'Generar enlace',
                'clase' => 'btn-primary',
            ],
            default => null,
        };

        abort_if($config === null, 404);

        $registro = [
            'titulo' => $usuario->name,
            'detalle' => $usuario->email . ' · Rol: ' . User::rolLabel($usuario->rol),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function guardarUsuario(Request $request, BitacoraService $bitacora): RedirectResponse
    {
        $rolesValidos = implode(',', array_keys(User::roles()));
        $estadosValidos = implode(',', array_keys(User::estados()));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', 'unique:users,email'],
            'rol' => ['required', "in:{$rolesValidos}"],
            'estado' => ['required', "in:{$estadosValidos}"],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $user = User::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'rol' => $data['rol'],
            'estado' => $data['estado'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => $data['estado'] === 'activo' ? now() : null,
        ]);

        $bitacora->registrar(
            'usuarios',
            'crear',
            "Se creó el usuario {$user->name} ({$user->email}) con rol {$user->rol}.",
        );

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function actualizarUsuario(Request $request, User $usuario, BitacoraService $bitacora): RedirectResponse
    {
        $rolesValidos = implode(',', array_keys(User::roles()));
        $estadosValidos = implode(',', array_keys(User::estados()));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', Rule::unique('users', 'email')->ignore($usuario->id)],
            'rol' => ['required', "in:{$rolesValidos}"],
            'estado' => ['required', "in:{$estadosValidos}"],
            'password' => ['nullable', 'confirmed', PasswordRule::defaults()],
        ]);

        $cambios = [];
        foreach (['name', 'email', 'rol', 'estado'] as $campo) {
            $nuevoValor = $campo === 'email'
                ? strtolower(trim($data[$campo]))
                : trim((string) $data[$campo]);

            if ($usuario->{$campo} !== $nuevoValor) {
                $cambios[] = $campo;
            }
        }

        $usuario->fill([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'rol' => $data['rol'],
            'estado' => $data['estado'],
        ]);

        if (! empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
            $cambios[] = 'password';
        }

        if ($usuario->estado === 'activo' && ! $usuario->email_verified_at) {
            $usuario->email_verified_at = now();
        }

        $usuario->save();

        $bitacora->registrar(
            'usuarios',
            'actualizar',
            'Se actualizaron los campos: ' . implode(', ', array_unique($cambios ?: ['sin cambios visibles'])) . ". Usuario: {$usuario->email}."
        );

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function cambiarEstadoUsuario(User $usuario, BitacoraService $bitacora): RedirectResponse
    {
        if (auth()->id() === $usuario->id) {
            return back()->with('error', 'No puedes bloquear tu propio acceso.');
        }

        $nuevoEstado = $usuario->estado === 'bloqueado' ? 'activo' : 'bloqueado';

        $usuario->update(['estado' => $nuevoEstado]);

        $bitacora->registrar(
            'usuarios',
            'cambio_estado',
            "El usuario {$usuario->email} cambió a estado {$nuevoEstado}."
        );

        return back()->with('success', $nuevoEstado === 'activo' ? 'Usuario desbloqueado.' : 'Usuario bloqueado.');
    }

    public function reenviarAcceso(User $usuario, BitacoraService $bitacora): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $usuario->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', 'No fue posible generar el enlace de acceso.');
        }

        $bitacora->registrar(
            'usuarios',
            'recuperacion',
            "Se generó un enlace de restablecimiento para {$usuario->email}."
        );

        return back()->with('success', 'Se generó el enlace de recuperación de acceso.');
    }
}
