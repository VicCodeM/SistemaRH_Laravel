<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SesionUnicaService
{
    public function contarSesionesActivas(User $usuario, ?string $sesionExcluida = null): int
    {
        if ($this->usaDriverDatabase() === false) {
            return 0;
        }

        return $this->querySesionesActivas($usuario, $sesionExcluida)->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sesionesActivasDetalle(User $usuario, ?string $sesionExcluida = null): array
    {
        if ($this->usaDriverDatabase() === false) {
            return [];
        }

        return $this->querySesionesActivas($usuario, $sesionExcluida)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($sesion) => $this->formatearSesion($sesion))
            ->all();
    }

    public function cerrarOtrasSesiones(User $usuario, string $sesionActualId): int
    {
        if ($this->usaDriverDatabase() === false || $sesionActualId === '') {
            return 0;
        }

        return $this->querySesionesActivas($usuario, $sesionActualId)->delete();
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function querySesionesActivas(User $usuario, ?string $sesionExcluida = null)
    {
        $consulta = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $usuario->getKey())
            ->where('last_activity', '>=', now()->subMinutes((int) config('session.lifetime', 30))->timestamp);

        if ($sesionExcluida !== null && $sesionExcluida !== '') {
            $consulta->where('id', '!=', $sesionExcluida);
        }

        return $consulta;
    }

    /**
     * @param object{id: string, ip_address?: ?string, user_agent?: ?string, last_activity?: int} $sesion
     * @return array<string, mixed>
     */
    private function formatearSesion(object $sesion): array
    {
        $userAgent = (string) ($sesion->user_agent ?? '');
        $ultimoAcceso = Carbon::createFromTimestamp((int) ($sesion->last_activity ?? now()->timestamp));

        return [
            'id' => (string) $sesion->id,
            'ip_address' => $this->formatearIp((string) ($sesion->ip_address ?? '')),
            'user_agent' => $userAgent,
            'navegador' => $this->detectarNavegador($userAgent),
            'sistema_operativo' => $this->detectarSistemaOperativo($userAgent),
            'dispositivo' => $this->describirDispositivo($userAgent),
            'ultima_actividad_at' => $ultimoAcceso->timestamp,
            'ultima_actividad_humana' => $ultimoAcceso
                ->locale(config('app.locale', 'es'))
                ->diffForHumans(),
            'ultima_actividad_formateada' => $ultimoAcceso->format('d/m/Y H:i'),
        ];
    }

    private function formatearIp(string $ip): string
    {
        return $ip !== '' ? $ip : 'Desconocida';
    }

    private function describirDispositivo(string $userAgent): string
    {
        $navegador = $this->detectarNavegador($userAgent);
        $sistemaOperativo = $this->detectarSistemaOperativo($userAgent);

        if ($navegador === 'Desconocido' && $sistemaOperativo === 'Desconocido') {
            return 'Dispositivo desconocido';
        }

        if ($navegador === 'Desconocido') {
            return $sistemaOperativo;
        }

        if ($sistemaOperativo === 'Desconocido') {
            return $navegador;
        }

        return "{$navegador} en {$sistemaOperativo}";
    }

    private function detectarNavegador(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') || str_contains($userAgent, 'Edge/') => 'Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Edg/') && ! str_contains($userAgent, 'OPR/') => 'Chrome',
            str_contains($userAgent, 'Safari/') && str_contains($userAgent, 'Version/') && ! str_contains($userAgent, 'Chrome/') && ! str_contains($userAgent, 'Chromium/') => 'Safari',
            str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/') => 'Internet Explorer',
            default => 'Desconocido',
        };
    }

    private function detectarSistemaOperativo(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows NT') => 'Windows',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iPod') => 'iOS',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Desconocido',
        };
    }

    private function usaDriverDatabase(): bool
    {
        return config('session.driver') === 'database';
    }
}
