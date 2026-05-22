<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardarSitioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->rol === 'admin';
    }

    public function rules(): array
    {
        return [
            // Identidad / SEO
            'sitio_nombre'      => ['required', 'string', 'max:120'],
            'sitio_descripcion' => ['nullable', 'string', 'max:300'],
            'favicon'           => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg,ico', 'max:1024'],
            'quitar_favicon'    => ['nullable', 'boolean'],

            // Hero
            'landing_hero_badge'     => ['nullable', 'string', 'max:120'],
            'landing_hero_titulo'    => ['required', 'string', 'max:200'],
            'landing_hero_acento'    => ['nullable', 'string', 'max:120'],
            'landing_hero_subtitulo' => ['nullable', 'string', 'max:400'],

            // Características
            'landing_feat_label'    => ['nullable', 'string', 'max:120'],
            'landing_feat_1_titulo' => ['nullable', 'string', 'max:120'],
            'landing_feat_1_texto'  => ['nullable', 'string', 'max:300'],
            'landing_feat_2_titulo' => ['nullable', 'string', 'max:120'],
            'landing_feat_2_texto'  => ['nullable', 'string', 'max:300'],
            'landing_feat_3_titulo' => ['nullable', 'string', 'max:120'],
            'landing_feat_3_texto'  => ['nullable', 'string', 'max:300'],
            'landing_feat_4_titulo' => ['nullable', 'string', 'max:120'],
            'landing_feat_4_texto'  => ['nullable', 'string', 'max:300'],
            'landing_feat_5_titulo' => ['nullable', 'string', 'max:120'],
            'landing_feat_5_texto'  => ['nullable', 'string', 'max:300'],

            // Footer
            'landing_footer' => ['nullable', 'string', 'max:200'],

            // Páginas legales
            'privacidad_contenido' => ['nullable', 'string', 'max:20000'],
            'terminos_contenido'   => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'sitio_nombre.required'      => 'El nombre del sitio es obligatorio.',
            'landing_hero_titulo.required' => 'El título principal es obligatorio.',
            'favicon.image'              => 'El favicon debe ser una imagen.',
            'favicon.mimes'              => 'El favicon debe ser PNG, JPG, WEBP, SVG o ICO.',
            'favicon.max'                => 'El favicon no debe pesar más de 1 MB.',
        ];
    }
}
