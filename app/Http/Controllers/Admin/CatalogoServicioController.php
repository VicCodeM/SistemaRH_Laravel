<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use Illuminate\Http\Request;

class CatalogoServicioController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.catalogos.index', array_merge(
            $request->except('page'),
            ['tab' => 'servicios']
        ));
    }

    public function create()
    {
        return view('admin.catalogo.form', ['servicio' => new CatalogoServicio()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::tipos()))],
            'flujo' => ['nullable', 'in:' . implode(',', array_keys(CatalogoServicio::flujos()))],
            'nivel_jerarquico' => ['nullable', 'in:' . implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()))],
            'para_quien' => ['nullable', 'in:empresa,candidato,ambos'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['flujo'] = $data['flujo'] ?? 'servicio';
        $data['para_quien'] = $data['para_quien'] ?? 'empresa';
        $data['nivel_jerarquico'] = CatalogoServicio::normalizarNivelJerarquico($data['nivel_jerarquico'] ?? 'todos');

        if ($data['flujo'] === 'vacante') {
            $data['para_quien'] = 'empresa';
            $data['nivel_jerarquico'] = 'todos';
        }

        $data['activo'] = $request->boolean('activo', true);

        $catalogo = CatalogoServicio::create($data);

        return redirect()->route('admin.catalogo.edit', $catalogo)
            ->with('success', 'Servicio creado correctamente. Ahora puedes agregar su presentacion.');
    }

    public function edit(CatalogoServicio $catalogo)
    {
        return view('admin.catalogo.form', ['servicio' => $catalogo]);
    }

    public function update(Request $request, CatalogoServicio $catalogo)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::tipos()))],
            'flujo' => ['nullable', 'in:' . implode(',', array_keys(CatalogoServicio::flujos()))],
            'nivel_jerarquico' => ['nullable', 'in:' . implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()))],
            'para_quien' => ['nullable', 'in:empresa,candidato,ambos'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['flujo'] = $data['flujo'] ?? 'servicio';
        $data['para_quien'] = $data['para_quien'] ?? 'empresa';
        $data['nivel_jerarquico'] = CatalogoServicio::normalizarNivelJerarquico($data['nivel_jerarquico'] ?? 'todos');

        if ($data['flujo'] === 'vacante') {
            $data['para_quien'] = 'empresa';
            $data['nivel_jerarquico'] = 'todos';
        }

        $data['activo'] = $request->boolean('activo');

        $catalogo->update($data);

        return redirect()->route('admin.catalogo.edit', $catalogo)
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function toggle(CatalogoServicio $catalogo)
    {
        if ($catalogo->activo && ! $catalogo->puedeDesactivarse()) {
            return back()->with('error', 'No se puede desactivar este servicio porque ya tiene pedidos activos o en proceso.');
        }

        $catalogo->update(['activo' => ! $catalogo->activo]);

        return back()->with('success', $catalogo->activo ? 'Servicio activado.' : 'Servicio desactivado.');
    }

    public function accionModal(CatalogoServicio $catalogo, string $accion)
    {
        abort_if($accion !== 'eliminar', 404);

        $config = [
            'titulo' => 'Eliminar servicio del catálogo',
            'descripcion' => 'El servicio dejará de aparecer para empresas y candidatos.',
            'mensaje' => 'Confirma si deseas eliminar este servicio del catálogo. Esta acción no se puede deshacer.',
            'ruta' => route('admin.catalogo.destroy', $catalogo),
            'metodo' => 'DELETE',
            'boton' => 'Eliminar servicio',
            'clase' => 'btn-danger',
        ];

        $registro = [
            'titulo' => $catalogo->nombre,
            'detalle' => (CatalogoServicio::tipos()[$catalogo->tipo] ?? $catalogo->tipo) . ' · ' . (CatalogoServicio::nivelJerarquicoLabel($catalogo->nivel_jerarquico)),
        ];

        return view('admin.partials.modal-accion', compact('config', 'registro'));
    }

    public function destroy(CatalogoServicio $catalogo)
    {
        if ($catalogo->tieneSolicitudesRelacionadas()) {
            return back()->with('error', 'No se puede eliminar este servicio porque ya tiene solicitudes asociadas. Desactivalo en su lugar.');
        }

        $catalogo->delete();

        return back()->with('success', 'Servicio eliminado.');
    }
}
