<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogoOpcion;
use App\Models\CatalogoServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CatalogoOpcionController extends Controller
{
    public function index(Request $request)
    {
        $tabActivo = $request->string('tab')->toString() ?: 'servicios';

        // Grupos editables del módulo activo (cada catálogo en su módulo)
        $gruposDelModulo = CatalogoOpcion::gruposDelModulo($tabActivo);

        $query = CatalogoOpcion::query()->whereIn('grupo', $gruposDelModulo);

        if ($request->filled('grupo') && in_array($request->grupo, $gruposDelModulo, true)) {
            $query->where('grupo', $request->grupo);
        }

        if ($request->filled('estado')) {
            $query->where('activo', $request->estado === 'activo');
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('clave', 'like', "%{$buscar}%")
                    ->orWhere('valor', 'like', "%{$buscar}%")
                    ->orWhere('descripcion', 'like', "%{$buscar}%");
            });
        }

        $catalogos = $query
            ->orderBy('grupo')
            ->orderBy('orden')
            ->orderBy('valor')
            ->get();

        $catalogosPorGrupo = $catalogos->groupBy('grupo');

        $serviciosQuery = CatalogoServicio::query();

        if ($request->filled('tipo')) {
            $serviciosQuery->where('tipo', $request->tipo);
        }

        if ($request->filled('nivel')) {
            $serviciosQuery->where('nivel_jerarquico', $request->nivel);
        }

        if ($request->filled('para_quien')) {
            $serviciosQuery->where('para_quien', $request->para_quien);
        }

        if ($request->filled('buscar_servicio')) {
            $buscarServicio = $request->buscar_servicio;
            $serviciosQuery->where(function ($q) use ($buscarServicio) {
                $q->where('nombre', 'like', "%{$buscarServicio}%")
                    ->orWhere('descripcion', 'like', "%{$buscarServicio}%");
            });
        }

        $servicios = $serviciosQuery
            ->orderBy('orden')
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'opciones_total' => CatalogoOpcion::count(),
            'grupos_activos' => CatalogoOpcion::distinct('grupo')->count('grupo'),
            'opciones_sistema' => CatalogoOpcion::where('es_sistema', true)->count(),
            'opciones_personalizadas' => CatalogoOpcion::where('es_sistema', false)->count(),
        ];

        $serviciosStats = [
            'total' => CatalogoServicio::count(),
            'activos' => CatalogoServicio::where('activo', true)->count(),
            'empresa' => CatalogoServicio::where('para_quien', 'empresa')->count(),
            'candidato' => CatalogoServicio::where('para_quien', 'candidato')->count(),
            'ambos' => CatalogoServicio::where('para_quien', 'ambos')->count(),
        ];

        return view('admin.catalogos.index', compact(
            'tabActivo',
            'catalogos',
            'catalogosPorGrupo',
            'stats',
            'servicios',
            'serviciosStats'
        ));
    }

    public function create()
    {
        return view('admin.catalogos.form', [
            'catalogo' => new CatalogoOpcion(),
            'grupos' => CatalogoOpcion::gruposGestionables(),
            'grupoInicial' => request('grupo'),
        ]);
    }

    public function store(Request $request)
    {
        $gruposValidos = implode(',', array_keys(CatalogoOpcion::gruposGestionables()));
        $request->merge([
            'clave' => Str::slug((string) $request->input('clave'), '_'),
        ]);

        $data = $request->validate([
            'grupo' => ['required', "in:{$gruposValidos}"],
            'clave' => [
                'required',
                'string',
                'max:100',
                Rule::unique('catalogo_opciones', 'clave')
                    ->where(fn ($query) => $query->where('grupo', $request->grupo)),
            ],
            'valor' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['es_sistema'] = false;
        $data['orden'] = $data['orden'] ?? 0;

        CatalogoOpcion::create($data);

        return redirect()->route('admin.catalogos.index')->with('success', 'Opción creada correctamente.');
    }

    public function edit(CatalogoOpcion $catalogo)
    {
        return view('admin.catalogos.form', [
            'catalogo' => $catalogo,
            'grupos' => CatalogoOpcion::gruposGestionables(),
            'grupoInicial' => $catalogo->grupo,
        ]);
    }

    public function update(Request $request, CatalogoOpcion $catalogo)
    {
        $gruposValidos = implode(',', array_keys(CatalogoOpcion::gruposGestionables()));

        $data = $request->validate([
            'grupo' => ['required', "in:{$gruposValidos}"],
            'valor' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['activo'] = $catalogo->es_sistema ? true : $request->boolean('activo');
        $data['orden'] = $data['orden'] ?? 0;

        $catalogo->update($data);

        return redirect()->route('admin.catalogos.index')->with('success', 'Opción actualizada correctamente.');
    }

    public function toggle(CatalogoOpcion $catalogo)
    {
        if ($catalogo->es_sistema) {
            return back()->with('error', 'Las opciones del sistema no se pueden desactivar.');
        }

        $catalogo->update(['activo' => ! $catalogo->activo]);

        return back()->with('success', $catalogo->activo ? 'Opción activada.' : 'Opción desactivada.');
    }

    public function accionModal(CatalogoOpcion $catalogo, string $accion)
    {
        abort_if($accion !== 'eliminar', 404);

        $config = [
            'titulo' => 'Eliminar opcion del catalogo',
            'descripcion' => 'La opcion dejara de aparecer en los formularios.',
            'mensaje' => 'Confirma si deseas eliminar esta opcion. Si ya existe en registros previos, esos valores historicos se conservaran.',
            'ruta' => route('admin.catalogos.destroy', $catalogo),
            'metodo' => 'DELETE',
            'boton' => 'Eliminar opcion',
            'clase' => 'btn-danger',
            'permitido' => ! $catalogo->es_sistema,
            'aviso' => $catalogo->es_sistema ? 'Las opciones del sistema no se pueden eliminar.' : null,
        ];

        $registro = [
            'titulo' => $catalogo->valor,
            'detalle' => CatalogoOpcion::grupoLabel($catalogo->grupo),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function destroy(CatalogoOpcion $catalogo)
    {
        if ($catalogo->es_sistema) {
            return back()->with('error', 'No se puede eliminar una opción del sistema.');
        }

        $catalogo->delete();

        return back()->with('success', 'Opción eliminada.');
    }
}
