<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatalogoServicio;
use App\Models\CatalogoOpcion;
use App\Models\PersonalExterno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PersonalExternoController extends Controller
{
    public function index(Request $request)
    {
        $query = PersonalExterno::latest();

        if ($request->filled('especialidad')) {
            $query->where('especialidad', $request->especialidad);
        }

        if ($request->filled('disponibilidad')) {
            $query->where('disponibilidad', $request->disponibilidad);
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellidos', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('empresa_o_razon_social', 'like', "%{$buscar}%");
            });
        }

        $personas = $query->paginate(15)->withQueryString();

        return view('admin.personal-externo.index', compact('personas'));
    }

    public function create()
    {
        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();
        $especialidades = CatalogoServicio::tipos();
        $disponibilidades = CatalogoOpcion::opciones('disponibilidad_externa', [
            'disponible' => 'Disponible',
            'ocupado' => 'Ocupado',
            'inactivo' => 'Inactivo',
        ]);

        return view('admin.personal-externo.form', [
            'persona' => new PersonalExterno(),
            'niveles' => $niveles,
            'especialidades' => $especialidades,
            'disponibilidades' => $disponibilidades,
        ]);
    }

    public function store(Request $request)
    {
        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));
        $disponibilidadesValidas = implode(',', array_keys(CatalogoOpcion::opciones('disponibilidad_externa', [
            'disponible' => 'Disponible',
            'ocupado' => 'Ocupado',
            'inactivo' => 'Inactivo',
        ])));

        $data = $request->validate([
            'nombre'                 => ['required', 'string', 'max:100'],
            'apellidos'              => ['required', 'string', 'max:150'],
            'email'                  => ['required', 'email', 'unique:personal_externo,email'],
            'telefono'               => ['nullable', 'string', 'max:20'],
            'especialidad'           => ['required', 'string'],
            'niveles_jerarquicos'    => ['required', 'array', 'min:1'],
            'niveles_jerarquicos.*'  => ['string', "in:{$nivelesValidos}"],
            'empresa_o_razon_social' => ['nullable', 'string', 'max:200'],
            'descripcion'            => ['nullable', 'string'],
            'disponibilidad'         => ['required', "in:{$disponibilidadesValidas}"],
            'cv'                     => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $data['niveles_jerarquicos'] = collect($data['niveles_jerarquicos'])
            ->map(fn ($nivel) => CatalogoServicio::normalizarNivelJerarquico($nivel))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($request->hasFile('cv')) {
            $data['cv_path'] = $request->file('cv')->store('cv-personal-externo', 'public');
        }

        PersonalExterno::create($data);

        return redirect()->route('admin.personal-externo.index')
            ->with('success', 'Personal externo registrado correctamente.');
    }

    public function edit(PersonalExterno $personalExterno)
    {
        $niveles = CatalogoServicio::nivelesJerarquicosFormulario();
        $especialidades = CatalogoServicio::tipos();
        $disponibilidades = CatalogoOpcion::opciones('disponibilidad_externa', [
            'disponible' => 'Disponible',
            'ocupado' => 'Ocupado',
            'inactivo' => 'Inactivo',
        ]);

        return view('admin.personal-externo.form', [
            'persona' => $personalExterno,
            'niveles' => $niveles,
            'especialidades' => $especialidades,
            'disponibilidades' => $disponibilidades,
        ]);
    }

    public function update(Request $request, PersonalExterno $personalExterno)
    {
        $nivelesValidos = implode(',', array_keys(CatalogoServicio::nivelesJerarquicosCompatibles()));
        $disponibilidadesValidas = implode(',', array_keys(CatalogoOpcion::opciones('disponibilidad_externa', [
            'disponible' => 'Disponible',
            'ocupado' => 'Ocupado',
            'inactivo' => 'Inactivo',
        ])));

        $data = $request->validate([
            'nombre'                 => ['required', 'string', 'max:100'],
            'apellidos'              => ['required', 'string', 'max:150'],
            'email'                  => ['required', 'email', "unique:personal_externo,email,{$personalExterno->id}"],
            'telefono'               => ['nullable', 'string', 'max:20'],
            'especialidad'           => ['required', 'string'],
            'niveles_jerarquicos'    => ['required', 'array', 'min:1'],
            'niveles_jerarquicos.*'  => ['string', "in:{$nivelesValidos}"],
            'empresa_o_razon_social' => ['nullable', 'string', 'max:200'],
            'descripcion'            => ['nullable', 'string'],
            'disponibilidad'         => ['required', "in:{$disponibilidadesValidas}"],
            'cv'                     => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $data['niveles_jerarquicos'] = collect($data['niveles_jerarquicos'])
            ->map(fn ($nivel) => CatalogoServicio::normalizarNivelJerarquico($nivel))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($request->hasFile('cv')) {
            if ($personalExterno->cv_path) {
                Storage::disk('public')->delete($personalExterno->cv_path);
            }

            $data['cv_path'] = $request->file('cv')->store('cv-personal-externo', 'public');
        }

        $personalExterno->update($data);

        return redirect()->route('admin.personal-externo.index')
            ->with('success', 'Datos actualizados correctamente.');
    }

    public function modal(PersonalExterno $personalExterno)
    {
        return view('admin.personal-externo.modal', ['persona' => $personalExterno]);
    }

    public function destroy(PersonalExterno $personalExterno)
    {
        if ($personalExterno->cv_path) {
            Storage::disk('public')->delete($personalExterno->cv_path);
        }

        $personalExterno->delete();

        return back()->with('success', 'Registro eliminado correctamente.');
    }
}
