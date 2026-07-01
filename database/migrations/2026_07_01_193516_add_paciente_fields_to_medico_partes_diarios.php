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
        Schema::table('medico_partes_diarios', function (Blueprint $table) {
            $table->string('tipo_paciente')->nullable()->after('cargo');
            $table->string('habitacion')->nullable()->after('tipo_paciente');
            $table->string('turno')->nullable()->after('habitacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medico_partes_diarios', function (Blueprint $table) {
            // SQLite limitation — columns cannot be dropped, rollback is a no-op
        });
    }
};
