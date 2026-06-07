<?php

namespace App\Models;

use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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

    private static function tablaDisponible(): bool
    {
        try {
            return Schema::hasTable((new static)->getTable());
        } catch (\Throwable) {
            return false;
        }
    }

    public static function boolean(string $clave, bool $default = false): bool
    {
        if (! static::tablaDisponible()) {
            return $default;
        }

        try {
            $valor = static::query()->where('clave', $clave)->value('valor');
        } catch (\Throwable) {
            return $default;
        }

        if ($valor === null) {
            return $default;
        }

        return in_array(strtolower(trim((string) $valor)), ['1', 'true', 'si', 's', 'yes', 'on'], true);
    }

    public static function texto(string $clave, ?string $default = null): ?string
    {
        if (! static::tablaDisponible()) {
            return $default;
        }

        try {
            $valor = static::query()->where('clave', $clave)->value('valor');
        } catch (\Throwable) {
            return $default;
        }

        if ($valor === null || $valor === '') {
            return $default;
        }

        return (string) $valor;
    }

    /**
     * Devuelve una lista almacenada como JSON o como texto con saltos de linea.
     *
     * @return array<int, string>
     */
    public static function arreglo(string $clave, array $default = []): array
    {
        if (! static::tablaDisponible()) {
            return $default;
        }

        try {
            $valor = static::query()->where('clave', $clave)->value('valor');
        } catch (\Throwable) {
            return $default;
        }

        if ($valor === null || $valor === '') {
            return $default;
        }

        $decodificado = json_decode((string) $valor, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decodificado)) {
            return array_values(array_filter(array_map(
                static fn ($item) => trim((string) $item),
                $decodificado
            ), static fn (string $item) => $item !== ''));
        }

        $lineas = preg_split('/\R/u', (string) $valor) ?: [];

        return array_values(array_filter(array_map('trim', $lineas), static fn (string $item) => $item !== ''));
    }

    public static function guardar(string $clave, mixed $valor, array $atributos = []): self
    {
        $tipo = $atributos['tipo'] ?? (is_bool($valor) ? 'boolean' : (is_array($valor) ? 'json' : 'string'));

        if (is_bool($valor)) {
            $valorNormalizado = $valor ? '1' : '0';
        } elseif (is_array($valor)) {
            $valorNormalizado = json_encode(array_values($valor), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
        } else {
            $valorNormalizado = (string) $valor;
        }

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
