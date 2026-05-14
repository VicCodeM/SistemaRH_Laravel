<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Candidato;
use App\Models\Vacante;

class WorkflowService
{
    public function modeFor(string $entity): string
    {
        return config("workflow.{$entity}", 'manual');
    }

    public function decideEmpresaRegistration(Empresa $empresa): string
    {
        $mode = $this->modeFor('empresas');
        if ($mode === 'manual') {
            return 'pendiente';
        }

        $rfc = strtoupper(trim($empresa->rfc));
        $telefonoDigits = preg_replace('/\D+/', '', (string) $empresa->telefono);

        if (
            trim($empresa->nombre_empresa) !== '' &&
            preg_match('/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/', $rfc) &&
            strlen($telefonoDigits) >= 10 &&
            trim($empresa->direccion) !== ''
        ) {
            $empresa->update(['estado' => 'activa']);
            return 'activa';
        }

        return 'pendiente';
    }

    public function decideCandidatoRegistration(Candidato $candidato): string
    {
        $mode = $this->modeFor('candidatos');
        if ($mode === 'manual') {
            return 'pendiente';
        }

        $completo = (
            trim($candidato->nombre ?? '') !== '' &&
            trim($candidato->apellido_paterno ?? '') !== '' &&
            trim($candidato->telefono ?? '') !== '' &&
            trim($candidato->curp ?? '') !== ''
        );

        if ($completo) {
            $candidato->update(['solicitud_estado' => 'aprobada']);
            return 'aprobada';
        }

        return 'pendiente';
    }

    public function decideVacanteCreation(Vacante $vacante): string
    {
        $mode = $this->modeFor('vacantes');
        if ($mode === 'manual') {
            return 'pendiente';
        }

        if (mb_strlen(trim($vacante->titulo)) >= 6 && mb_strlen(trim($vacante->descripcion ?? '')) >= 30) {
            $vacante->update(['estado' => 'activa']);
            return 'activa';
        }

        return 'pendiente';
    }
}
