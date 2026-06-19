<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico_archivos_importados', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('nombre_original');
            $table->string('nombre_guardado');
            $table->string('ruta');
            $table->string('extension', 10);
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->string('estado', 30)->default('recibido');
            $table->unsignedInteger('total_filas')->default(0);
            $table->unsignedInteger('filas_importadas')->default(0);
            $table->unsignedInteger('filas_con_error')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_subida')->nullable();
            $table->timestamp('fecha_procesado')->nullable();
            $table->timestamps();
        });

        Schema::create('medico_partes_diarios', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archivo_importado_id')->constrained('medico_archivos_importados')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('nombres');
            $table->integer('edad')->nullable();
            $table->string('area')->nullable();
            $table->string('cargo')->nullable();
            $table->string('certificados')->nullable();
            $table->string('subsidio')->nullable();
            $table->float('horas_certificado')->nullable();
            $table->integer('dias_certificado')->nullable();
            $table->date('fecha_inicio_certificado')->nullable();
            $table->date('fecha_fin_certificado')->nullable();
            $table->string('medico_certifica')->nullable();
            $table->string('causa')->nullable();
            $table->text('diagnostico')->nullable();
            $table->string('medicamento_1')->nullable();
            $table->string('medicamento_2')->nullable();
            $table->string('medicamento_3')->nullable();
            $table->text('observacion')->nullable();
            $table->string('hash_unico', 64)->unique();
            $table->timestamps();

            $table->index(['fecha', 'area']);
            $table->index(['fecha', 'causa']);
            $table->index('nombres');
        });

        Schema::create('medico_kardex', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archivo_importado_id')->constrained('medico_archivos_importados')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('tipo', 20)->default('medicina');
            $table->string('nombre');
            $table->float('saldo_anterior')->default(0);
            $table->float('ingresos')->default(0);
            $table->float('egresos')->default(0);
            $table->float('total')->default(0);
            $table->string('fecha_caducidad')->nullable();
            $table->string('hash_unico', 64)->unique();
            $table->timestamps();

            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('tipo');
        });

        Schema::create('medico_importacion_errores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archivo_importado_id')->constrained('medico_archivos_importados')->cascadeOnDelete();
            $table->unsignedInteger('fila');
            $table->string('columna')->nullable();
            $table->string('valor')->nullable();
            $table->text('mensaje');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_importacion_errores');
        Schema::dropIfExists('medico_kardex');
        Schema::dropIfExists('medico_partes_diarios');
        Schema::dropIfExists('medico_archivos_importados');
    }
};
