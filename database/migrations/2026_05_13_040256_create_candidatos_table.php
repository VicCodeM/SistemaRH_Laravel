<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            // Datos Personales Básicos
            $table->string('nombre', 150)->nullable();
            $table->string('apellido_paterno', 100)->nullable();
            $table->string('apellido_materno', 100)->nullable();
            $table->integer('edad')->nullable();
            $table->enum('sexo', ['M','F','Otro'])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('lugar_nacimiento', 150)->nullable();
            $table->string('nacionalidad', 100)->nullable();
            
            // Datos de Contacto y Domicilio
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('domicilio', 255)->nullable();
            $table->string('colonia', 150)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('municipio', 150)->nullable();
            $table->string('ciudad', 120)->nullable();
            
            // Datos Físicos y Familiares
            $table->string('peso', 20)->nullable();
            $table->string('estatura', 20)->nullable();
            $table->string('vive_con', 100)->nullable();
            $table->string('estado_civil', 50)->nullable();
            $table->text('dependientes')->nullable();
            
            // Documentos Oficiales
            $table->string('curp', 20)->nullable();
            $table->string('nore_seguro_social', 30)->nullable();
            $table->string('rfc', 20)->nullable();
            $table->string('afore', 100)->nullable();
            $table->string('cartilla_militar', 50)->nullable();
            $table->string('pasaporte', 50)->nullable();
            
            // Datos Profesionales
            $table->integer('experiencia_anios')->default(0);
            $table->string('puesto_deseado', 150)->nullable();
            $table->text('habilidades')->nullable();
            $table->string('escolaridad', 150)->nullable();
            $table->string('sueldo_deseado', 50)->nullable();
            $table->string('sueldo_aprobado', 50)->nullable();
            $table->date('fecha_contratacion')->nullable();
            $table->string('cv_path', 255)->nullable();
            
            // Estado de la Solicitud
            $table->enum('solicitud_estado', ['borrador','enviada','en_revision','aprobada','rechazada'])->default('borrador');
            $table->timestamp('solicitud_enviada_at')->nullable();
            $table->timestamp('solicitud_revisada_at')->nullable();
            $table->foreignId('solicitud_revision_admin_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Campos JSON (Como en el sistema anterior)
            $table->json('licencia_conducir')->nullable();
            $table->json('redes_sociales')->nullable();
            $table->json('estado_salud')->nullable();
            $table->json('datos_personales')->nullable();
            $table->json('datos_familiares')->nullable();
            $table->json('escolaridad_detallada')->nullable();
            $table->json('conocimientos_generales')->nullable();
            $table->json('historial_laboral')->nullable();
            $table->json('referencias_personales')->nullable();
            $table->json('datos_generales')->nullable();
            $table->json('datos_economicos')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatos');
    }
};
