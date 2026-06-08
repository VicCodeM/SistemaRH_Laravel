<?php

namespace App\Console\Commands;

use App\Models\CatalogoServicioRecurso;
use App\Models\VacanteRecurso;
use App\Services\ImagenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class OptimizarImagenes extends Command
{
    protected $signature = 'imagenes:optimizar';

    protected $description = 'Re-comprime las imagenes existentes de presentaciones a WebP ligero (sirve para optimizar las que se subieron antes).';

    public function handle(ImagenService $imagenService): int
    {
        $disk = Storage::disk('public');
        $modelos = [
            'Servicios' => CatalogoServicioRecurso::class,
            'Vacantes'  => VacanteRecurso::class,
        ];

        $total = 0;
        $optimizadas = 0;
        $bytesAntes = 0;
        $bytesDespues = 0;

        foreach ($modelos as $etiqueta => $modelo) {
            if (! Schema::hasTable((new $modelo)->getTable())) {
                continue;
            }

            $this->line("== {$etiqueta} ==");

            foreach ($modelo::whereNotNull('archivo_path')->cursor() as $recurso) {
                $total++;
                $rutaVieja = $recurso->archivo_path;
                $thumbViejo = $recurso->thumb_path;
                $antes = (int) ($recurso->tamano_bytes ?: 0);

                $resultado = $imagenService->optimizarRuta($rutaVieja);

                if (! $resultado) {
                    $this->warn("  - Saltado #{$recurso->id} (no es imagen o falta el archivo): {$rutaVieja}");
                    continue;
                }

                // Borra el archivo viejo si cambio de nombre/extension (ej. .png -> .webp).
                if ($resultado['path'] !== $rutaVieja && $disk->exists($rutaVieja)) {
                    $disk->delete($rutaVieja);
                }
                if ($thumbViejo && $thumbViejo !== $resultado['thumb_path'] && $disk->exists($thumbViejo)) {
                    $disk->delete($thumbViejo);
                }

                $recurso->update([
                    'archivo_path' => $resultado['path'],
                    'thumb_path' => $resultado['thumb_path'],
                    'tamano_bytes' => $resultado['tamano'],
                    'mime_type' => $resultado['mime_type'],
                ]);

                $optimizadas++;
                $bytesAntes += $antes;
                $bytesDespues += (int) $resultado['tamano'];

                $this->line("  - OK #{$recurso->id}: " . $this->kb($antes) . ' -> ' . $this->kb($resultado['tamano']));
            }
        }

        $ahorro = max(0, $bytesAntes - $bytesDespues);
        $this->newLine();
        $this->info("Listo. Optimizadas {$optimizadas} de {$total} imagenes. Ahorro aproximado: " . $this->kb($ahorro) . '.');

        return self::SUCCESS;
    }

    private function kb(int $bytes): string
    {
        if ($bytes <= 0) {
            return '-';
        }

        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1) . ' MB'
            : number_format($bytes / 1024, 0) . ' KB';
    }
}
