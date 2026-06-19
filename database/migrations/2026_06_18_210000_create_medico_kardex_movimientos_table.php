<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico_kardex_movimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kardex_id')->constrained('medico_kardex')->cascadeOnDelete();
            $table->foreignId('parte_diario_id')->nullable()->constrained('medico_partes_diarios')->nullOnDelete();
            $table->foreignId('archivo_importado_id')->nullable()->constrained('medico_archivos_importados')->nullOnDelete();
            $table->string('medicamento_nombre');
            $table->string('campo_medicamento', 30)->nullable();
            $table->float('cantidad')->default(1);
            $table->string('tipo', 20)->default('salida');
            $table->float('saldo_resultante')->default(0);
            $table->date('fecha_movimiento');
            $table->string('personal_responsable')->nullable();
            $table->string('hash_unico', 64)->unique();
            $table->timestamps();

            $table->index(['kardex_id', 'fecha_movimiento']);
            $table->index('tipo');
            $table->index('medicamento_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_kardex_movimientos');
    }
};
