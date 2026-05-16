<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicioAsignado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class PersonalInternoController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('rol', 'interno')
            ->withCount([
                'serviciosAsignados as tareas_activas' => fn ($q) => $q->whereIn('estado', ['activo', 'en_proceso']),
                'serviciosAsignados as tareas_completadas' => fn ($q) => $q->where('estado', 'completado'),
            ]);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$buscar}%")
                ->orWhere('email', 'like', "%{$buscar}%"));
        }

        $internos = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total'     => User::where('rol', 'interno')->count(),
            'activos'   => User::where('rol', 'interno')->where('estado', 'activo')->count(),
            'tareas_abiertas' => ServicioAsignado::whereIn('estado', ['activo', 'en_proceso'])
                ->whereHas('asignadoA', fn ($q) => $q->where('rol', 'interno'))
                ->count(),
        ];

        return view('admin.personal-interno.index', compact('internos', 'stats'));
    }

    public function create()
    {
        return view('admin.personal-interno.form', ['interno' => new User()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $usuario = User::create([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'rol'    => 'interno',
            'estado' => 'activo',
            'password' => Hash::make(Str::random(20)),
            'email_verified_at' => now(),
        ]);

        Password::sendResetLink(['email' => $usuario->email]);

        return redirect()
            ->route('admin.personal-interno.index')
            ->with('success', "Interno \"{$usuario->name}\" creado. Se envió un enlace de acceso a {$usuario->email}.");
    }

    public function modal(User $interno)
    {
        abort_unless($interno->rol === 'interno', 403);
        $interno->loadCount([
            'serviciosAsignados as tareas_activas' => fn ($q) => $q->whereIn('estado', ['activo', 'en_proceso']),
            'serviciosAsignados as tareas_completadas' => fn ($q) => $q->where('estado', 'completado'),
        ]);
        $tareas = $interno->serviciosAsignados()
            ->with('servicio')
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.personal-interno.modal', compact('interno', 'tareas'));
    }

    public function toggleEstado(User $interno)
    {
        abort_unless($interno->rol === 'interno', 403);
        $nuevoEstado = $interno->estado === 'activo' ? 'bloqueado' : 'activo';
        $interno->update(['estado' => $nuevoEstado]);

        $msg = $nuevoEstado === 'activo' ? 'Interno activado correctamente.' : 'Interno desactivado.';
        return back()->with('success', $msg);
    }
}
