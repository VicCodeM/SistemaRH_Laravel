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
            'nivel_jerarquico' => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()))],
            'para_quien' => ['required', 'in:empresa,candidato,ambos'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['nivel_jerarquico'] = CatalogoServicio::normalizarNivelJerarquico($data['nivel_jerarquico']);
        $data['activo'] = $request->boolean('activo', true);

        CatalogoServicio::create($data);

        return redirect()->route('admin.catalogos.index', ['tab' => 'servicios'])
            ->with('success', 'Servicio creado correctamente.');
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
            'nivel_jerarquico' => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()))],
            'para_quien' => ['required', 'in:empresa,candidato,ambos'],
            'activo' => ['boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['nivel_jerarquico'] = CatalogoServicio::normalizarNivelJerarquico($data['nivel_jerarquico']);
        $data['activo'] = $request->boolean('activo');

        $catalogo->update($data);

        return redirect()->route('admin.catalogos.index', ['tab' => 'servicios'])
            ->with('success', 'Servicio actualizado correctamente.');
    }

    public function toggle(CatalogoServicio $catalogo)
    {
        $catalogo->update(['activo' => ! $catalogo->activo]);

        return back()->with('success', $catalogo->activo ? 'Servicio activado.' : 'Servicio desactivado.');
    }

    public function destroy(CatalogoServicio $catalogo)
    {
        $catalogo->delete();

        return back()->with('success', 'Servicio eliminado.');
    }
}
