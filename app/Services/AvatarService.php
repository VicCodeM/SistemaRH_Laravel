<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Maneja avatares (foto de perfil / logo) con compresión y redimensionado.
 *
 * Usa GD nativo de PHP — sin librerías externas.
 * Estrategia: redimensionar manteniendo proporción al lado máx (default 400px)
 * y reguardar como JPEG con calidad 85 para reducir tamaño.
 */
class AvatarService
{
    public const TAMANO_MAX_PX = 400;
    public const CALIDAD_JPEG  = 85;
    public const CARPETA       = 'avatars';

    /**
     * Sube y procesa una imagen. Devuelve la ruta relativa guardada en disk 'public'.
     */
    public function guardar(UploadedFile $archivo): string
    {
        $imagen = $this->cargarImagen($archivo);
        if (! $imagen) {
            throw new \RuntimeException('No se pudo procesar la imagen. Usa JPG, PNG o WEBP.');
        }

        $redimensionada = $this->redimensionar($imagen, self::TAMANO_MAX_PX);

        // Guardamos siempre como JPEG (peso menor que PNG, sin transparencia que importe en avatar)
        $nombre = self::CARPETA . '/' . Str::random(40) . '.jpg';
        $rutaCompleta = Storage::disk('public')->path($nombre);

        // Asegurar que el directorio existe
        if (! is_dir(dirname($rutaCompleta))) {
            mkdir(dirname($rutaCompleta), 0755, true);
        }

        imagejpeg($redimensionada, $rutaCompleta, self::CALIDAD_JPEG);

        imagedestroy($imagen);
        imagedestroy($redimensionada);

        return $nombre;
    }

    /**
     * Elimina un avatar local previo si existe.
     */
    public function eliminar(?string $rutaRelativa): void
    {
        if (! $rutaRelativa || str_starts_with($rutaRelativa, 'http')) {
            return;
        }

        if (Storage::disk('public')->exists($rutaRelativa)) {
            Storage::disk('public')->delete($rutaRelativa);
        }
    }

    private function cargarImagen(UploadedFile $archivo): \GdImage|false
    {
        $contenido = file_get_contents($archivo->getRealPath());
        return @imagecreatefromstring($contenido);
    }

    /**
     * Redimensiona la imagen al tamaño máximo manteniendo proporción.
     */
    private function redimensionar(\GdImage $original, int $tamanoMax): \GdImage
    {
        $w = imagesx($original);
        $h = imagesy($original);

        // Si ya es menor al máximo, no redimensionar
        if ($w <= $tamanoMax && $h <= $tamanoMax) {
            return $this->normalizar($original, $w, $h);
        }

        // Calcular nuevo tamaño manteniendo proporción
        if ($w > $h) {
            $nuevoW = $tamanoMax;
            $nuevoH = (int) round($h * ($tamanoMax / $w));
        } else {
            $nuevoH = $tamanoMax;
            $nuevoW = (int) round($w * ($tamanoMax / $h));
        }

        return $this->normalizar($original, $nuevoW, $nuevoH);
    }

    /**
     * Crea imagen final con fondo blanco (evita fondos negros si era PNG transparente).
     */
    private function normalizar(\GdImage $original, int $w, int $h): \GdImage
    {
        $destino = imagecreatetruecolor($w, $h);

        // Fondo blanco (para transparencias)
        $blanco = imagecolorallocate($destino, 255, 255, 255);
        imagefilledrectangle($destino, 0, 0, $w, $h, $blanco);

        imagecopyresampled(
            $destino, $original,
            0, 0, 0, 0,
            $w, $h,
            imagesx($original), imagesy($original)
        );

        return $destino;
    }
}
