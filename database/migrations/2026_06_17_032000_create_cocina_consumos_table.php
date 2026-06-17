<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cocina_archivos_importados', function (Blueprint $table): void {
            $table->unsignedInteger('total_filas')->default(0)->after('estado');
            $table->unsignedInteger('filas_importadas')->default(0)->after('total_filas');
            $table->unsignedInteger('filas_con_error')->default(0)->after('filas_importadas');
            $table->timestamp('fecha_procesado')->nullable()->after('fecha_subida');
        });

        Schema::create('cocina_productos', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->nullable()->unique();
            $table->string('nombre');
            $table->string('unidad_medida', 50)->nullable();
            $table->string('grupo')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['nombre', 'activo']);
        });

        Schema::create('cocina_consumos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archivo_importado_id')->constrained('cocina_archivos_importados')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('cocina_productos');
            $table->date('fecha');
            $table->string('servicio', 30)->default('desayuno');
            $table->string('concepto')->nullable();
            $table->string('unidad_medida', 50)->nullable();
            $table->decimal('cantidad', 14, 3);
            $table->decimal('valor', 14, 2)->nullable();
            $table->string('hash_unico', 64)->unique();
            $table->timestamps();

            $table->index(['fecha', 'servicio']);
            $table->index(['producto_id', 'fecha']);
        });

        Schema::create('cocina_importacion_errores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('archivo_importado_id')->constrained('cocina_archivos_importados')->cascadeOnDelete();
            $table->unsignedInteger('fila');
            $table->string('columna')->nullable();
            $table->string('valor')->nullable();
            $table->text('mensaje');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cocina_importacion_errores');
        Schema::dropIfExists('cocina_consumos');
        Schema::dropIfExists('cocina_productos');

        Schema::table('cocina_archivos_importados', function (Blueprint $table): void {
            $table->dropColumn(['total_filas', 'filas_importadas', 'filas_con_error', 'fecha_procesado']);
        });
    }
};
