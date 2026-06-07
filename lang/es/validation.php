<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser un correo electrónico válido.',
    'string' => 'El campo :attribute debe ser texto.',
    'max' => [
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'unique' => 'El :attribute ya está registrado.',
    'in' => 'El :attribute seleccionado no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'numeric' => 'El campo :attribute debe ser un número.',
    'nullable' => 'El campo :attribute puede estar vacío.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'file' => 'El campo :attribute debe ser un archivo.',

    'attributes' => [
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
        'name' => 'nombre',
        'titulo' => 'título',
        'requerimientos' => 'requerimientos',
    ],
];
