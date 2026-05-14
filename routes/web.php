<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CatalogoServicioController;
use App\Http\Controllers\Admin\PersonalExternoController;
use App\Http\Controllers\Empresa\EmpresaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard — role-based redirect hub
    Route::get('/dashboard', function () {
        $rol = auth()->user()->rol;
        return match($rol) {
            'admin'     => redirect()->route('admin.dashboard'),
            'empresa'   => redirect()->route('empresa.dashboard'),
            'candidato' => redirect()->route('candidato.solicitud'),
            default     => view('dashboard'),
        };
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Panel
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/empresas', [AdminController::class, 'empresas'])->name('empresas');
        Route::get('/empresas/{empresa}/modal', [AdminController::class, 'showEmpresa'])->name('empresas.modal');
        Route::patch('/empresas/{empresa}/aprobar', [AdminController::class, 'aprobarEmpresa'])->name('empresas.aprobar');
        Route::patch('/empresas/{empresa}/rechazar', [AdminController::class, 'rechazarEmpresa'])->name('empresas.rechazar');
        Route::patch('/empresas/{empresa}/suspender', [AdminController::class, 'suspenderEmpresa'])->name('empresas.suspender');

        Route::get('/candidatos', [AdminController::class, 'candidatos'])->name('candidatos');
        Route::get('/candidatos/{candidato}/modal', [AdminController::class, 'showCandidato'])->name('candidatos.modal');
        Route::patch('/candidatos/{candidato}/aprobar', [AdminController::class, 'aprobarCandidato'])->name('candidatos.aprobar');
        Route::patch('/candidatos/{candidato}/rechazar', [AdminController::class, 'rechazarCandidato'])->name('candidatos.rechazar');

        Route::get('/vacantes', [AdminController::class, 'vacantes'])->name('vacantes');
        Route::get('/vacantes/crear', [AdminController::class, 'crearVacante'])->name('vacantes.crear');
        Route::post('/vacantes', [AdminController::class, 'guardarVacante'])->name('vacantes.guardar');
        Route::patch('/vacantes/{vacante}/activar', [AdminController::class, 'activarVacante'])->name('vacantes.activar');
        Route::patch('/vacantes/{vacante}/cerrar', [AdminController::class, 'cerrarVacante'])->name('vacantes.cerrar');
        Route::get('/vacantes/{vacante}/editar', [AdminController::class, 'editarVacante'])->name('vacantes.editar');
        Route::put('/vacantes/{vacante}', [AdminController::class, 'actualizarVacante'])->name('vacantes.actualizar');
        Route::get('/vacantes/{vacante}/matching', [AdminController::class, 'matchingVacante'])->name('vacantes.matching');
        Route::post('/vacantes/{vacante}/asignar', [AdminController::class, 'asignarCandidato'])->name('vacantes.asignar');
        Route::patch('/postulaciones/{postulacion}/estado', [AdminController::class, 'moverPostulacion'])->name('postulaciones.mover');

        // Catálogo de servicios
        Route::resource('catalogo', CatalogoServicioController::class)->except(['show']);
        Route::patch('/catalogo/{catalogo}/toggle', [CatalogoServicioController::class, 'toggle'])->name('catalogo.toggle');

        // Personal externo
        Route::resource('personal-externo', PersonalExternoController::class)->except(['show']);
        Route::get('/personal-externo/{personalExterno}/modal', [PersonalExternoController::class, 'modal'])->name('personal-externo.modal');
    });

    // Empresa Panel
    Route::middleware(['role:empresa'])->prefix('empresa')->name('empresa.')->group(function () {
        Route::get('/dashboard', [EmpresaController::class, 'dashboard'])->name('dashboard');

        // Mis Servicios (solicitudes)
        Route::get('/solicitudes', [EmpresaController::class, 'solicitudes'])->name('solicitudes');
        Route::get('/solicitudes/crear', [EmpresaController::class, 'crearSolicitud'])->name('solicitudes.crear');
        Route::post('/solicitudes', [EmpresaController::class, 'guardarSolicitud'])->name('solicitudes.guardar');
        Route::get('/solicitudes/{vacante}', [EmpresaController::class, 'verSolicitud'])->name('solicitudes.ver');
        Route::get('/solicitudes/{vacante}/editar', [EmpresaController::class, 'editarSolicitud'])->name('solicitudes.editar');
        Route::put('/solicitudes/{vacante}', [EmpresaController::class, 'actualizarSolicitud'])->name('solicitudes.actualizar');

        Route::patch('/postulaciones/{postulacion}/mover', [EmpresaController::class, 'moverPostulacion'])->name('postulaciones.mover');
    });

    // Candidato
    Route::middleware(['role:candidato'])->prefix('candidato')->name('candidato.')->group(function () {
        Route::get('/solicitud', fn () => view('candidato.solicitud'))->name('solicitud');
        Route::get('/vacantes', function () {
            $vacantes = \App\Models\Vacante::where('estado', 'activa')
                ->with('empresa')
                ->orderBy('fecha_publicacion', 'desc')
                ->paginate(12);
            return view('candidato.vacantes', compact('vacantes'));
        })->name('vacantes');
        Route::get('/postulaciones', function () {
            $candidato = Auth::user()->candidato;
            $postulaciones = $candidato
                ? \App\Models\Postulacion::where('candidato_id', $candidato->id)
                    ->with('vacante.empresa')
                    ->orderBy('fecha_postulacion', 'desc')
                    ->get()
                : collect();
            return view('candidato.postulaciones', compact('postulaciones'));
        })->name('postulaciones');
        Route::post('/vacantes/{vacante}/postular', function (\App\Models\Vacante $vacante) {
            $user = Auth::user();
            $candidato = $user->candidato;
            if (!$candidato || $candidato->solicitud_estado !== 'enviada') {
                return back()->with('error', 'Debes completar y enviar tu solicitud primero.');
            }
            $existe = \App\Models\Postulacion::where('candidato_id', $candidato->id)
                ->where('vacante_id', $vacante->id)->exists();
            if ($existe) {
                return back()->with('error', 'Ya te postulaste a esta vacante.');
            }
            \App\Models\Postulacion::create([
                'candidato_id' => $candidato->id,
                'vacante_id' => $vacante->id,
                'estado' => 'postulado',
                'fecha_postulacion' => now(),
            ]);
            return redirect()->route('candidato.postulaciones')->with('success', '¡Postulación exitosa!');
        })->name('postular');
    });

    // Tickets (empresa y admin)
    Route::prefix('tickets')->name('tickets.')->middleware('role:empresa,admin')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/crear', [TicketController::class, 'crear'])->name('crear');
        Route::post('/', [TicketController::class, 'guardar'])->name('guardar');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/responder', [TicketController::class, 'responder'])->name('responder');
        Route::patch('/{ticket}/estado', [TicketController::class, 'cambiarEstado'])->name('estado');
    });

    // Chat (todos los roles autenticados)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', fn () => view('chat.index'))->name('index');
        Route::get('/{room}', fn (\App\Models\ChatRoom $room) => view('chat.show', compact('room')))->name('show');
    });

    // Interno Panel
    Route::middleware(['role:interno'])->prefix('interno')->name('interno.')->group(function () {
        Route::get('/dashboard', fn () => view('interno.dashboard'))->name('dashboard');
    });
});

require __DIR__.'/auth.php';
