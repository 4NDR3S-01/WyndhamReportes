<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico_catalogos', function (Blueprint $table): void {
            $table->id();
            $table->string('tipo', 80);
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['tipo', 'nombre']);
            $table->index(['tipo', 'activo']);
        });

        Schema::create('medico_pacientes', function (Blueprint $table): void {
            $table->id();
            $table->string('cedula', 30)->nullable();
            $table->string('nombres');
            $table->unsignedInteger('edad')->nullable();
            $table->string('area')->nullable();
            $table->string('cargo')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->text('patologias')->nullable();
            $table->string('tipo', 40)->default('colaborador');
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('cedula');
            $table->index('nombres');
            $table->index(['area', 'cargo']);
        });

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

        Schema::table('medico_kardex_movimientos', function (Blueprint $table): void {
            $table->foreignId('producto_id')->nullable()->after('kardex_id')->constrained('medico_productos')->nullOnDelete();
            $table->string('origen', 40)->default('parte_diario')->after('tipo');
            $table->text('observacion')->nullable()->after('personal_responsable');
        });

        Schema::create('medico_parte_medicamentos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parte_diario_id')->constrained('medico_partes_diarios')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('medico_productos')->nullOnDelete();
            $table->foreignId('movimiento_id')->nullable()->constrained('medico_kardex_movimientos')->nullOnDelete();
            $table->string('campo_origen', 30)->nullable();
            $table->string('nombre_original');
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->boolean('procesado')->default(false);
            $table->text('observacion')->nullable();
            $table->timestamps();

            $table->index(['parte_diario_id', 'producto_id']);
            $table->index('nombre_original');
        });

        Schema::create('medico_kardex_cierres', function (Blueprint $table): void {
            $table->id();
            $table->string('periodo', 7)->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado', 20)->default('abierto');
            $table->foreignId('generado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cerrado_en')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['fecha_inicio', 'fecha_fin']);
            $table->index(['periodo', 'estado']);
        });

        Schema::create('medico_kardex_cierre_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cierre_id')->constrained('medico_kardex_cierres')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('medico_productos')->nullOnDelete();
            $table->string('tipo', 30)->default('medicina');
            $table->string('nombre');
            $table->decimal('saldo_anterior', 12, 2)->default(0);
            $table->decimal('ingresos', 12, 2)->default(0);
            $table->decimal('egresos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->date('fecha_caducidad')->nullable();
            $table->timestamps();

            $table->unique(['cierre_id', 'nombre']);
            $table->index(['tipo', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_kardex_cierre_items');
        Schema::dropIfExists('medico_kardex_cierres');
        Schema::dropIfExists('medico_parte_medicamentos');

        Schema::table('medico_kardex_movimientos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('producto_id');
            $table->dropColumn(['origen', 'observacion']);
        });

        Schema::dropIfExists('medico_producto_aliases');
        Schema::dropIfExists('medico_productos');
        Schema::dropIfExists('medico_pacientes');
        Schema::dropIfExists('medico_catalogos');
    }
};
