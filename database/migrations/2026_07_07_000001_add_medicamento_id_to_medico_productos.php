<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_productos', function (Blueprint $table): void {
            $table->foreignId('medicamento_id')->nullable()->after('id')
                ->constrained('medicamentos')->nullOnDelete();
        });

        // Vincular productos existentes con medicamentos por coincidencia de nombre
        DB::statement("
            UPDATE medico_productos mp
            SET medicamento_id = (
                SELECT m.id FROM medicamentos m
                WHERE LOWER(m.nombre) = LOWER(mp.nombre)
                LIMIT 1
            )
            WHERE mp.medicamento_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('medico_productos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('medicamento_id');
        });
    }
};
