<?php

namespace App\Services;

use App\Models\ConfiguracionSistema;
use App\Models\User;
use Illuminate\Support\Str;

class AccesoMunicipioService
{
    public const CLAVE_TODOS = 'acceso_municipios_todos';
    public const CLAVE_PERMITIDOS = 'acceso_municipios_permitidos';

    public function permiteTodos(): bool
    {
        return ConfiguracionSistema::boolean(self::CLAVE_TODOS, true);
    }

    /**
     * @return array<int, string>
     */
    public function municipiosPermitidos(): array
    {
        return ConfiguracionSistema::arreglo(self::CLAVE_PERMITIDOS, []);
    }

    public function municipiosPermitidosTexto(): string
    {
        return implode(PHP_EOL, $this->municipiosPermitidos());
    }

    /**
     * @param array<int, string> $municipios
     */
    public function guardarConfiguracion(bool $permitirTodos, array $municipios): void
    {
        ConfiguracionSistema::guardar(self::CLAVE_TODOS, $permitirTodos, [
            'grupo' => 'accesos',
            'tipo' => 'boolean',
            'descripcion' => 'Permite que todos los municipios entren al sistema.',
            'orden' => 20,
        ]);

        ConfiguracionSistema::guardar(self::CLAVE_PERMITIDOS, $municipios, [
            'grupo' => 'accesos',
            'tipo' => 'json',
            'descripcion' => 'Lista de municipios autorizados cuando la restriccion esta activa.',
            'orden' => 30,
        ]);
    }

    public function municipioUsuario(User $user): ?string
    {
        if ($user->esAdmin() || $user->esInterno()) {
            return null;
        }

        $municipio = match ($user->rol) {
            'empresa' => $user->empresa?->municipio,
            'candidato' => $user->candidato?->municipio,
            default => null,
        };

        $municipio = is_string($municipio) ? trim($municipio) : '';

        return $municipio !== '' ? $municipio : null;
    }

    public function puedeAcceder(User $user): bool
    {
        if ($user->esAdmin() || $user->esInterno()) {
            return true;
        }

        if ($this->permiteTodos()) {
            return true;
        }

        $municipioUsuario = $this->municipioUsuario($user);
        if ($municipioUsuario === null) {
            return false;
        }

        return $this->municipioPermitido($municipioUsuario);
    }

    public function municipioPermitido(string $municipio): bool
    {
        if ($this->permiteTodos()) {
            return true;
        }

        $municipioNormalizado = $this->normalizar($municipio);

        foreach ($this->municipiosPermitidos() as $permitido) {
            if ($this->normalizar($permitido) === $municipioNormalizado) {
                return true;
            }
        }

        return false;
    }

    public function mensajeDenegado(User $user): string
    {
        $municipio = $this->municipioUsuario($user);

        if ($municipio === null) {
            return 'Tu cuenta no tiene un municipio registrado. Contacta al administrador para completar ese dato.';
        }

        return "Tu municipio ({$municipio}) no está autorizado para acceder al sistema.";
    }

    /**
     * @param array<int, string> $municipios
     * @return array<int, string>
     */
    public function normalizarLista(array $municipios): array
    {
        $resultado = [];
        $vistos = [];

        foreach ($municipios as $municipio) {
            $limpio = trim((string) $municipio);
            if ($limpio === '') {
                continue;
            }

            $clave = $this->normalizar($limpio);
            if (isset($vistos[$clave])) {
                continue;
            }

            $vistos[$clave] = true;
            $resultado[] = $limpio;
        }

        return $resultado;
    }

    private function normalizar(string $valor): string
    {
        return (string) Str::of($valor)->ascii()->lower()->trim();
    }
}
