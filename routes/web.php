<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CatalogoOpcionController;
use App\Http\Controllers\Admin\CatalogoServicioController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\ServicioAsignadoController;
use App\Http\Controllers\Admin\PersonalExternoController;
use App\Http\Controllers\Admin\PersonalInternoController;
use App\Http\Controllers\Candidato\CandidatoController;
use App\Http\Controllers\Interno\InternoController;
use App\Http\Controllers\Interno\TareaController;
use App\Http\Controllers\Empresa\EmpresaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $rol = auth()->user()->rol;

        return match ($rol) {
            'admin'     => redirect()->route('admin.dashboard'),
            'empresa'   => redirect()->route('empresa.dashboard'),
            'candidato' => redirect()->route('candidato.dashboard'),
            'interno'   => redirect()->route('interno.dashboard'),
            default     => redirect()->route('admin.dashboard'),
        };
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/reportes', [AdminController::class, 'reportes'])->name('reportes');
        Route::get('/reportes/exportar', [AdminController::class, 'exportarCsv'])->name('reportes.exportar');

        Route::get('/empresas', [AdminController::class, 'empresas'])->name('empresas');
        Route::get('/empresas/{empresa}/modal', [AdminController::class, 'showEmpresa'])->name('empresas.modal');
        Route::get('/empresas/{empresa}/rechazar/modal', [AdminController::class, 'rechazarEmpresaModal'])->name('empresas.rechazar.modal');
        Route::patch('/empresas/{empresa}/aprobar', [AdminController::class, 'aprobarEmpresa'])->name('empresas.aprobar');
        Route::patch('/empresas/{empresa}/rechazar', [AdminController::class, 'rechazarEmpresa'])->name('empresas.rechazar');
        Route::patch('/empresas/{empresa}/suspender', [AdminController::class, 'suspenderEmpresa'])->name('empresas.suspender');
        Route::delete('/empresas/{empresa}', [AdminController::class, 'destroyEmpresa'])->name('empresas.destroy');

        Route::get('/candidatos', [AdminController::class, 'candidatos'])->name('candidatos');
        Route::get('/candidatos/{candidato}/modal', [AdminController::class, 'showCandidato'])->name('candidatos.modal');
        Route::get('/candidatos/{candidato}/solicitud', [AdminController::class, 'editarSolicitudCandidato'])->name('candidatos.solicitud');
        Route::get('/candidatos/{candidato}/rechazar/modal', [AdminController::class, 'rechazarCandidatoModal'])->name('candidatos.rechazar.modal');
        Route::patch('/candidatos/{candidato}/aprobar', [AdminController::class, 'aprobarCandidato'])->name('candidatos.aprobar');
        Route::patch('/candidatos/{candidato}/rechazar', [AdminController::class, 'rechazarCandidato'])->name('candidatos.rechazar');
        Route::delete('/candidatos/{candidato}', [AdminController::class, 'destroyCandidato'])->name('candidatos.destroy');

        Route::get('/vacantes', [AdminController::class, 'vacantes'])->name('vacantes');
        Route::get('/vacantes/crear', [AdminController::class, 'crearVacante'])->name('vacantes.crear');
        Route::post('/vacantes', [AdminController::class, 'guardarVacante'])->name('vacantes.guardar');
        Route::patch('/vacantes/{vacante}/activar', [AdminController::class, 'activarVacante'])->name('vacantes.activar');
        Route::patch('/vacantes/{vacante}/cerrar', [AdminController::class, 'cerrarVacante'])->name('vacantes.cerrar');
        Route::delete('/vacantes/{vacante}', [AdminController::class, 'destroyVacante'])->name('vacantes.destroy');
        Route::get('/vacantes/{vacante}/editar', [AdminController::class, 'editarVacante'])->name('vacantes.editar');
        Route::put('/vacantes/{vacante}', [AdminController::class, 'actualizarVacante'])->name('vacantes.actualizar');
        Route::get('/vacantes/{vacante}/matching', [AdminController::class, 'matchingVacante'])->name('vacantes.matching');
        Route::post('/vacantes/{vacante}/asignar', [AdminController::class, 'asignarCandidato'])->name('vacantes.asignar');
        Route::patch('/postulaciones/{postulacion}/estado', [AdminController::class, 'moverPostulacion'])->name('postulaciones.mover');

        Route::resource('catalogos', CatalogoOpcionController::class)->except(['show']);
        Route::patch('/catalogos/{catalogo}/toggle', [CatalogoOpcionController::class, 'toggle'])->name('catalogos.toggle');

        Route::resource('catalogo', CatalogoServicioController::class)->except(['show']);
        Route::patch('/catalogo/{catalogo}/toggle', [CatalogoServicioController::class, 'toggle'])->name('catalogo.toggle');

        Route::get('/buscar', [AdminController::class, 'buscarGlobal'])->name('buscar');
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion');
        Route::post('/configuracion/parametros', [ConfiguracionController::class, 'guardarParametros'])->name('configuracion.parametros.guardar');
        Route::get('/configuracion/usuarios/nuevo', [ConfiguracionController::class, 'nuevoUsuarioModal'])->name('configuracion.usuarios.nuevo');
        Route::post('/configuracion/usuarios', [ConfiguracionController::class, 'guardarUsuario'])->name('configuracion.usuarios.guardar');
        Route::get('/configuracion/usuarios/{usuario}/modal', [ConfiguracionController::class, 'usuarioModal'])->name('configuracion.usuarios.modal');
        Route::patch('/configuracion/usuarios/{usuario}', [ConfiguracionController::class, 'actualizarUsuario'])->name('configuracion.usuarios.actualizar');
        Route::patch('/configuracion/usuarios/{usuario}/estado', [ConfiguracionController::class, 'cambiarEstadoUsuario'])->name('configuracion.usuarios.estado');
        Route::post('/configuracion/usuarios/{usuario}/recuperar', [ConfiguracionController::class, 'reenviarAcceso'])->name('configuracion.usuarios.recuperar');

        Route::get('/tareas', [ServicioAsignadoController::class, 'index'])->name('tareas.index');
        Route::get('/tareas/crear', [ServicioAsignadoController::class, 'create'])->name('tareas.crear');
        Route::post('/tareas', [ServicioAsignadoController::class, 'store'])->name('tareas.guardar');
        Route::get('/tareas/{tarea}', [ServicioAsignadoController::class, 'show'])->name('tareas.show');
        Route::get('/tareas/{tarea}/editar', [ServicioAsignadoController::class, 'edit'])->name('tareas.editar');
        Route::put('/tareas/{tarea}', [ServicioAsignadoController::class, 'update'])->name('tareas.actualizar');
        Route::delete('/tareas/{tarea}', [ServicioAsignadoController::class, 'destroy'])->name('tareas.eliminar');

        Route::resource('personal-externo', PersonalExternoController::class)->except(['show']);
        Route::get('/personal-externo/{personalExterno}/modal', [PersonalExternoController::class, 'modal'])->name('personal-externo.modal');

        Route::get('/personal-interno', [PersonalInternoController::class, 'index'])->name('personal-interno.index');
        Route::get('/personal-interno/nuevo', [PersonalInternoController::class, 'create'])->name('personal-interno.crear');
        Route::post('/personal-interno', [PersonalInternoController::class, 'store'])->name('personal-interno.guardar');
        Route::get('/personal-interno/{interno}/modal', [PersonalInternoController::class, 'modal'])->name('personal-interno.modal');
        Route::patch('/personal-interno/{interno}/estado', [PersonalInternoController::class, 'toggleEstado'])->name('personal-interno.estado');
    });

    Route::middleware(['role:empresa'])->prefix('empresa')->name('empresa.')->group(function () {
        Route::get('/dashboard', [EmpresaController::class, 'dashboard'])->name('dashboard');

        Route::get('/solicitudes', [EmpresaController::class, 'solicitudes'])->name('solicitudes');
        Route::get('/solicitudes/crear', [EmpresaController::class, 'crearSolicitud'])->name('solicitudes.crear');
        Route::post('/solicitudes', [EmpresaController::class, 'guardarSolicitud'])->name('solicitudes.guardar');
        Route::get('/solicitudes/{vacante}', [EmpresaController::class, 'verSolicitud'])->name('solicitudes.ver');
        Route::get('/solicitudes/{vacante}/editar', [EmpresaController::class, 'editarSolicitud'])->name('solicitudes.editar');
        Route::put('/solicitudes/{vacante}', [EmpresaController::class, 'actualizarSolicitud'])->name('solicitudes.actualizar');

        Route::patch('/postulaciones/{postulacion}/mover', [EmpresaController::class, 'moverPostulacion'])->name('postulaciones.mover');

        Route::get('/vacantes', [EmpresaController::class, 'solicitudes'])->name('vacantes');
        Route::get('/vacantes/crear', [EmpresaController::class, 'crearSolicitud'])->name('vacantes.crear');
        Route::post('/vacantes', [EmpresaController::class, 'guardarSolicitud'])->name('vacantes.guardar');
        Route::get('/vacantes/{vacante}', [EmpresaController::class, 'verSolicitud'])->name('vacantes.ver');
        Route::get('/vacantes/{vacante}/editar', [EmpresaController::class, 'editarSolicitud'])->name('vacantes.editar');
        Route::put('/vacantes/{vacante}', [EmpresaController::class, 'actualizarSolicitud'])->name('vacantes.actualizar');
    });

    Route::middleware(['role:candidato'])->prefix('candidato')->name('candidato.')->group(function () {
        Route::get('/dashboard', [CandidatoController::class, 'dashboard'])->name('dashboard');
        Route::get('/solicitud', [CandidatoController::class, 'solicitud'])->name('solicitud');
        Route::get('/vacantes', [CandidatoController::class, 'vacantes'])->name('vacantes');
        Route::get('/vacantes/{vacante}/modal', [CandidatoController::class, 'vacanteModal'])->name('vacantes.modal');
        Route::get('/postulaciones', [CandidatoController::class, 'postulaciones'])->name('postulaciones');
        Route::post('/vacantes/{vacante}/postular', [CandidatoController::class, 'postular'])->name('postular');
    });

    Route::prefix('tickets')->name('tickets.')->middleware('role:empresa,admin,interno')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/crear', [TicketController::class, 'crear'])->name('crear');
        Route::post('/', [TicketController::class, 'guardar'])->name('guardar');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/responder', [TicketController::class, 'responder'])->name('responder');
        Route::patch('/{ticket}/estado', [TicketController::class, 'cambiarEstado'])->name('estado');
        Route::patch('/{ticket}/asignar', [TicketController::class, 'asignar'])->name('asignar');
    });

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', fn () => view('chat.index'))->name('index');
        Route::get('/{room}', fn (\App\Models\ChatRoom $room) => view('chat.show', compact('room')))->name('show');
    });

    Route::middleware(['role:interno'])->prefix('interno')->name('interno.')->group(function () {
        Route::get('/dashboard', [InternoController::class, 'dashboard'])->name('dashboard');
        Route::get('/tareas', [TareaController::class, 'index'])->name('tareas.index');
        Route::get('/tareas/{tarea}', [TareaController::class, 'show'])->name('tareas.show');
        Route::get('/tareas/{tarea}/tomar/modal', [TareaController::class, 'tomarModal'])->name('tareas.tomar.modal');
        Route::get('/tareas/{tarea}/completar/modal', [TareaController::class, 'completarModal'])->name('tareas.completar.modal');
        Route::get('/tareas/{tarea}/cancelar/modal', [TareaController::class, 'cancelarModal'])->name('tareas.cancelar.modal');
        Route::patch('/tareas/{tarea}/tomar', [TareaController::class, 'tomar'])->name('tareas.tomar');
        Route::patch('/tareas/{tarea}/completar', [TareaController::class, 'completar'])->name('tareas.completar');
        Route::patch('/tareas/{tarea}/cancelar', [TareaController::class, 'cancelar'])->name('tareas.cancelar');
    });
});

require __DIR__ . '/auth.php';
