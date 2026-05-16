<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistemas';

    protected $fillable = [
        'clave',
        'grupo',
        'tipo',
        'valor',
        'descripcion',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public static function boolean(string $clave, bool $default = false): bool
    {
        $valor = static::query()->where('clave', $clave)->value('valor');

        if ($valor === null) {
            return $default;
        }

        return in_array(strtolower(trim((string) $valor)), ['1', 'true', 'si', 's', 'yes', 'on'], true);
    }

    public static function texto(string $clave, ?string $default = null): ?string
    {
        $valor = static::query()->where('clave', $clave)->value('valor');

        if ($valor === null || $valor === '') {
            return $default;
        }

        return (string) $valor;
    }

    public static function guardar(string $clave, mixed $valor, array $atributos = []): self
    {
        $tipo = $atributos['tipo'] ?? (is_bool($valor) ? 'boolean' : 'string');
        $valorNormalizado = is_bool($valor) ? ($valor ? '1' : '0') : (string) $valor;

        return static::query()->updateOrCreate(
            ['clave' => $clave],
            [
                'grupo' => $atributos['grupo'] ?? 'general',
                'tipo' => $tipo,
                'valor' => $valorNormalizado,
                'descripcion' => $atributos['descripcion'] ?? null,
                'activo' => $atributos['activo'] ?? true,
                'orden' => $atributos['orden'] ?? 0,
            ]
        );
    }
}
