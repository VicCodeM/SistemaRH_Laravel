<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use Illuminate\Http\Request;

class CatalogoServicioController extends Controller
{
    public function index(Request $request)
    {
        $query = CatalogoServicio::orderBy('orden')->orderBy('tipo');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('nivel')) {
            $query->where('nivel_jerarquico', $request->nivel);
        }
        if ($request->filled('para_quien')) {
            $query->where('para_quien', $request->para_quien);
        }

        $servicios = $query->paginate(20)->withQueryString();

        return view('admin.catalogo.index', compact('servicios'));
    }

    public function create()
    {
        return view('admin.catalogo.form', ['servicio' => new CatalogoServicio()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => ['required', 'string', 'max:200'],
            'descripcion'      => ['nullable', 'string'],
            'tipo'             => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::tipos()))],
            'nivel_jerarquico' => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::nivelesJerarquicos()))],
            'para_quien'       => ['required', 'in:empresa,candidato,ambos'],
            'activo'           => ['boolean'],
            'orden'            => ['nullable', 'integer', 'min:0'],
        ]);

        $data['activo'] = $request->boolean('activo', true);

        CatalogoServicio::create($data);

        return redirect()->route('admin.catalogo.index')
            ->with('success', 'Servicio creado correctamente.');
    }

    public function edit(CatalogoServicio $catalogo)
    {
        return view('admin.catalogo.form', ['servicio' => $catalogo]);
    }

    public function update(Request $request, CatalogoServicio $catalogo)
    {
        $data = $request->validate([
            'nombre'           => ['required', 'string', 'max:200'],
            'descripcion'      => ['nullable', 'string'],
            'tipo'             => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::tipos()))],
            'nivel_jerarquico' => ['required', 'in:' . implode(',', array_keys(CatalogoServicio::nivelesJerarquicos()))],
            'para_quien'       => ['required', 'in:empresa,candidato,ambos'],
            'activo'           => ['boolean'],
            'orden'            => ['nullable', 'integer', 'min:0'],
        ]);

        $data['activo'] = $request->boolean('activo');

        $catalogo->update($data);

        return redirect()->route('admin.catalogo.index')
            ->with('success', 'Servicio actualizado.');
    }

    public function toggle(CatalogoServicio $catalogo)
    {
        $catalogo->update(['activo' => !$catalogo->activo]);

        return back()->with('success', $catalogo->activo ? 'Servicio activado.' : 'Servicio desactivado.');
    }

    public function destroy(CatalogoServicio $catalogo)
    {
        $catalogo->delete();
        return back()->with('success', 'Servicio eliminado.');
    }
}
