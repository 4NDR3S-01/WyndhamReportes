<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_partes_diarios', function (Blueprint $table): void {
            $table->foreignId('tipo_salida_id')->nullable()->after('diagnostico_id')->constrained('tipos_salida')->nullOnDelete();
            $table->foreignId('incidente_id')->nullable()->after('tipo_salida_id')->constrained('incidentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medico_partes_diarios', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tipo_salida_id');
            $table->dropConstrainedForeignId('incidente_id');
        });
    }
};
