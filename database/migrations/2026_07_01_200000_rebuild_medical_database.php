<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. CATÁLOGOS (tablas de referencia independientes)
        // ============================================================

        Schema::create('areas', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cargos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('causas', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('diagnosticos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('medicamentos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tipos_certificado', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('entidades_certificado', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tipos_descanso', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('tipos_salida', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // cirugias_generales, flebologias_vasculares, atenciones_medicas eliminadas (Fase 4)
        // Catálogos huérfanos sin FK desde ninguna otra tabla.

        Schema::create('incidentes', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // ============================================================
        // 2. PACIENTES Y FICHAS MÉDICAS
        // ============================================================

        Schema::create('medico_pacientes', function (Blueprint $table): void {
            $table->id();
            $table->string('cedula', 30)->nullable();
            $table->string('nombres');
            $table->unsignedInteger('edad')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->date('fecha_ingreso')->nullable();
            $table->text('patologias')->nullable();
            $table->string('tipo', 40)->default('colaborador'); // colaborador|aspirante|externo|paciente|huesped
            $table->boolean('activo')->default(true);
            $table->string('telefono', 50)->nullable();
            $table->text('vacunas')->nullable();
            $table->string('fichas_anteriores')->nullable();
            $table->text('antecedentes')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('cedula');
            $table->index('nombres');
            $table->index(['area_id', 'cargo_id']);
            $table->index(['tipo', 'activo']);
        });

        Schema::create('medico_paciente_examenes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paciente_id')->constrained('medico_pacientes')->cascadeOnDelete();
            $table->string('tipo', 30); // espirometria|ecografia|audiometria|optometria
            $table->date('fecha')->nullable();
            $table->timestamps();

            $table->unique(['paciente_id', 'tipo']);
        });

        Schema::create('medico_paciente_visitas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paciente_id')->constrained('medico_pacientes')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio'); // 2021-2026
            $table->date('fecha')->nullable();
            $table->timestamps();

            $table->unique(['paciente_id', 'anio']);
        });

        // ============================================================
        // 3. PARTES DIARIOS
        // ============================================================

        Schema::create('medico_partes_diarios', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha');
            $table->string('nombres');
            $table->unsignedInteger('edad')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->nullOnDelete();
            $table->string('tipo_paciente', 40)->nullable();
            $table->string('habitacion', 20)->nullable();
            $table->string('turno', 20)->nullable();
            // Certificado
            $table->foreignId('tipo_certificado_id')->nullable()->constrained('tipos_certificado')->nullOnDelete();
            $table->foreignId('entidad_certificado_id')->nullable()->constrained('entidades_certificado')->nullOnDelete();
            $table->decimal('horas_certificado', 8, 2)->nullable();
            $table->unsignedInteger('dias_certificado')->nullable();
            $table->date('fecha_inicio_certificado')->nullable();
            $table->date('fecha_fin_certificado')->nullable();
            $table->string('medico_certifica')->nullable();
            // Atención
            $table->foreignId('causa_id')->nullable()->constrained('causas')->nullOnDelete();
            $table->foreignId('diagnostico_id')->nullable()->constrained('diagnosticos')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->string('hash_unico', 64)->unique();
            $table->timestamps();

            $table->index(['fecha', 'area_id']);
            $table->index(['fecha', 'causa_id']);
            $table->index('nombres');
        });

        Schema::create('medico_parte_medicamentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parte_diario_id')->constrained('medico_partes_diarios')->cascadeOnDelete();
            $table->foreignId('medicamento_id')->nullable()->constrained('medicamentos')->nullOnDelete();
            $table->string('nombre_original');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->timestamps();

            $table->index(['parte_diario_id', 'medicamento_id']);
            $table->index('nombre_original');
        });

        // ============================================================
        // 5. PRODUCTOS E INVENTARIO
        // ============================================================

        Schema::create('medico_productos', function (Blueprint $table): void {
            $table->id();
            $table->string('tipo', 30)->default('medicina');
            $table->string('nombre')->unique();
            $table->decimal('stock_minimo', 12, 2)->default(0);
            $table->date('fecha_caducidad')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'activo']);
        });

        Schema::create('medico_producto_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('producto_id')->constrained('medico_productos')->cascadeOnDelete();
            $table->string('alias');
            $table->string('alias_normalizado')->unique();
            $table->timestamps();

            $table->index('alias');
        });

        // ============================================================
        // 6. KARDEX (inventario mensual)
        // ============================================================

        Schema::create('medico_kardex', function (Blueprint $table): void {
            $table->id();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('tipo', 20)->default('medicina');
            $table->string('nombre');
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('ingresos', 12, 2)->default(0);
            $table->decimal('egresos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('fecha_caducidad')->nullable();
            $table->string('hash_unico', 64)->unique();
            $table->timestamps();

            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('tipo');
        });

        Schema::create('medico_kardex_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kardex_id')->constrained('medico_kardex')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('medico_productos')->nullOnDelete();
            $table->string('tipo', 20); // entrada|salida
            $table->string('origen', 40)->default('parte_diario');
            $table->decimal('cantidad', 12, 2)->default(0);
            $table->date('fecha_movimiento')->nullable();
            $table->string('personal_responsable')->nullable();
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index('fecha_movimiento');
        });

        // Tablas medico_kardex_cierres / medico_kardex_cierre_items eliminadas (Fase 3)
        // El kardex mensual ahora se calcula en memoria desde los movimientos.

    }

    public function down(): void
    {
        Schema::dropIfExists('medico_kardex_movimientos');
        Schema::dropIfExists('medico_kardex');
        Schema::dropIfExists('medico_producto_aliases');
        Schema::dropIfExists('medico_productos');
        Schema::dropIfExists('medico_parte_medicamentos');
        Schema::dropIfExists('medico_partes_diarios');
        Schema::dropIfExists('medico_paciente_visitas');
        Schema::dropIfExists('medico_paciente_examenes');
        Schema::dropIfExists('medico_pacientes');
        Schema::dropIfExists('incidentes');
        Schema::dropIfExists('tipos_salida');
        Schema::dropIfExists('tipos_descanso');
        Schema::dropIfExists('entidades_certificado');
        Schema::dropIfExists('tipos_certificado');
        Schema::dropIfExists('medicamentos');
        Schema::dropIfExists('diagnosticos');
        Schema::dropIfExists('causas');
        Schema::dropIfExists('cargos');
        Schema::dropIfExists('areas');
    }
};
