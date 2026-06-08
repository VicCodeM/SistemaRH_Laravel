<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImagenService
{
    // Tamaño optimizado para web: suficiente para verse nítido en pantalla,
    // pero ligero para cargar rápido y no saturar el almacenamiento.
    private int $anchoMax = 1600;
    private int $altoMax = 1000;
    private int $calidad = 78;
    private int $thumbAncho = 320;
    private int $thumbAlto = 200;
    private int $thumbCalidad = 68;

    public function procesar(UploadedFile $archivo, string $carpeta): array
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        $imagen = $this->crearDesdeArchivo($archivo->getPathname(), $extension);

        if (!$imagen) {
            $ruta = $archivo->store($carpeta, 'public');
            return [
                'path' => $ruta,
                'thumb_path' => null,
                'tamano' => $archivo->getSize(),
                'mime_type' => $archivo->getClientMimeType(),
            ];
        }

        $nombreBase = pathinfo($archivo->hashName(), PATHINFO_FILENAME);

        return $this->guardarOptimizado($imagen, $carpeta, $nombreBase);
    }

    /**
     * Re-optimiza una imagen YA guardada en el disco public (re-comprime a WebP ligero
     * y regenera su miniatura). Se usa para optimizar imagenes subidas anteriormente.
     * Devuelve las rutas nuevas o null si el archivo no existe / no es imagen.
     */
    public function optimizarRuta(string $pathRelativo): ?array
    {
        $disk = Storage::disk('public');

        if (! $pathRelativo || ! $disk->exists($pathRelativo)) {
            return null;
        }

        $extension = strtolower(pathinfo($pathRelativo, PATHINFO_EXTENSION));
        $imagen = $this->crearDesdeArchivo($disk->path($pathRelativo), $extension);

        if (! $imagen) {
            return null;
        }

        $carpeta = str_replace('\\', '/', dirname($pathRelativo));
        $carpeta = $carpeta === '.' ? '' : $carpeta;
        $nombreBase = pathinfo($pathRelativo, PATHINFO_FILENAME);

        return $this->guardarOptimizado($imagen, $carpeta, $nombreBase);
    }

    /**
     * Redimensiona si hace falta, guarda como WebP ligero y genera la miniatura.
     */
    private function guardarOptimizado(\GdImage $imagen, string $carpeta, string $nombreBase): array
    {
        $anchoOriginal = imagesx($imagen);
        $altoOriginal = imagesy($imagen);

        if ($anchoOriginal > $this->anchoMax || $altoOriginal > $this->altoMax) {
            $imagen = $this->redimensionar($imagen, $anchoOriginal, $altoOriginal, $this->anchoMax, $this->altoMax);
        }

        $prefijo = $carpeta !== '' ? $carpeta . '/' : '';

        $rutaPrincipal = $prefijo . $nombreBase . '.webp';
        $rutaAbsoluta = Storage::disk('public')->path($rutaPrincipal);
        $this->asegurarDirectorio($rutaAbsoluta);
        imagewebp($imagen, $rutaAbsoluta, $this->calidad);
        $tamano = filesize($rutaAbsoluta);

        $thumb = $this->redimensionar($imagen, imagesx($imagen), imagesy($imagen), $this->thumbAncho, $this->thumbAlto);
        $rutaThumb = $prefijo . 'thumbs/' . $nombreBase . '.webp';
        $rutaThumbAbs = Storage::disk('public')->path($rutaThumb);
        $this->asegurarDirectorio($rutaThumbAbs);
        imagewebp($thumb, $rutaThumbAbs, $this->thumbCalidad);

        imagedestroy($imagen);
        imagedestroy($thumb);

        return [
            'path' => $rutaPrincipal,
            'thumb_path' => $rutaThumb,
            'tamano' => $tamano,
            'mime_type' => 'image/webp',
        ];
    }

    private function crearDesdeArchivo(string $ruta, string $extension): ?\GdImage
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($ruta) ?: null,
            'png' => $this->crearDesdePng($ruta),
            'webp' => @imagecreatefromwebp($ruta) ?: null,
            'gif' => @imagecreatefromgif($ruta) ?: null,
            default => null,
        };
    }

    private function crearDesdePng(string $ruta): ?\GdImage
    {
        $img = @imagecreatefrompng($ruta);
        if (!$img) return null;

        $ancho = imagesx($img);
        $alto = imagesy($img);
        $canvas = imagecreatetruecolor($ancho, $alto);
        $blanco = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $blanco);
        imagecopy($canvas, $img, 0, 0, 0, 0, $ancho, $alto);
        imagedestroy($img);

        return $canvas;
    }

    private function redimensionar(\GdImage $imagen, int $anchoOrig, int $altoOrig, int $anchoMax, int $altoMax): \GdImage
    {
        $ratio = min($anchoMax / $anchoOrig, $altoMax / $altoOrig);

        if ($ratio >= 1) {
            $copia = imagecreatetruecolor($anchoOrig, $altoOrig);
            imagecopy($copia, $imagen, 0, 0, 0, 0, $anchoOrig, $altoOrig);
            return $copia;
        }

        $nuevoAncho = (int) round($anchoOrig * $ratio);
        $nuevoAlto = (int) round($altoOrig * $ratio);

        $nueva = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        imagecopyresampled($nueva, $imagen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOrig, $altoOrig);

        return $nueva;
    }

    private function asegurarDirectorio(string $rutaArchivo): void
    {
        $dir = dirname($rutaArchivo);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
