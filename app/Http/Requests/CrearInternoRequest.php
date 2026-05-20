<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearInternoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'                   => ['required', 'string', 'max:255'],
            'email'                  => ['required', 'email', 'max:255', 'unique:users,email'],
            'capacidad_maxima_horas' => ['nullable', 'integer', 'min:1', 'max:80'],
            'departamento'           => ['nullable', 'string', 'max:100'],
            'disponibilidad'         => ['nullable', 'in:disponible,de_licencia,fuera'],
            'servicios'              => ['nullable', 'array'],
            'servicios.*'            => ['integer', 'exists:catalogo_servicios,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'El correo no tiene un formato válido.',
            'email.unique'   => 'Ya existe un usuario con ese correo.',
        ];
    }
}
