<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogoServicioRecurso extends Model
{
    protected $table = 'catalogo_servicio_recursos';

    protected $fillable = [
        'catalogo_servicio_id',
        'user_id',
        'tipo',
        'titulo',
        'descripcion',
        'archivo_path',
        'archivo_original',
        'mime_type',
        'tamano_bytes',
        'orden',
    ];

    protected $casts = [
        'tamano_bytes' => 'integer',
        'orden' => 'integer',
    ];

    public function catalogoServicio(): BelongsTo
    {
        return $this->belongsTo(CatalogoServicio::class, 'catalogo_servicio_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->archivo_path);
    }

    public function contenidoTexto(): ?string
    {
        if (! $this->esTexto() || ! $this->archivo_path) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->archivo_path)) {
            return null;
        }

        return Storage::disk('public')->get($this->archivo_path);
    }

    public function modoCarga(): string
    {
        return $this->esTexto() ? 'texto' : 'archivo';
    }

    public function extension(): string
    {
        return strtolower(pathinfo($this->archivo_original ?: $this->archivo_path, PATHINFO_EXTENSION));
    }

    public function esImagen(): bool
    {
        return Str::startsWith((string) ($this->mime_type ?? ''), 'image/');
    }

    public function esPdf(): bool
    {
        return $this->mime_type === 'application/pdf' || $this->extension() === 'pdf';
    }

    public function esTexto(): bool
    {
        $mime = (string) ($this->mime_type ?? '');

        return $mime === 'text/plain' || in_array($this->extension(), ['txt', 'md', 'csv'], true);
    }

    public function tipoLabel(): string
    {
        return match ($this->tipo) {
            'presentacion' => "Presentaci\u{00F3}n",
            'archivo' => 'Archivo',
            default => ucfirst(str_replace('_', ' ', (string) $this->tipo)),
        };
    }

    public function tipoBadgeClass(): string
    {
        return match ($this->tipo) {
            'presentacion' => 'badge-blue',
            'archivo' => 'badge-gray',
            default => 'badge-gray',
        };
    }

    public function tamanoHumano(): string
    {
        $bytes = (int) ($this->tamano_bytes ?? 0);

        if ($bytes <= 0) {
            return '-';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $valor = (float) $bytes;

        while ($valor >= 1024 && $i < count($unidades) - 1) {
            $valor /= 1024;
            $i++;
        }

        return $i === 0
            ? number_format($valor, 0) . ' ' . $unidades[$i]
            : number_format($valor, 1) . ' ' . $unidades[$i];
    }

    public function icono(): string
    {
        return match (true) {
            $this->esImagen() => 'IMG',
            $this->esPdf() => 'PDF',
            $this->esTexto() => 'TXT',
            $this->tipo === 'presentacion' => 'PPT',
            default => strtoupper(Str::of($this->extension() ?: 'file')->substr(0, 3)->toString()),
        };
    }
}
