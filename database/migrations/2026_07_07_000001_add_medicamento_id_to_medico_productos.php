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
        // Se usa PHP en lugar de SQL crudo para compatibilidad SQLite/MySQL
        $productos = DB::table('medico_productos')
            ->whereNull('medicamento_id')
            ->get(['id', 'nombre']);

        foreach ($productos as $producto) {
            $medicamento = DB::table('medicamentos')
                ->where(DB::raw('LOWER(nombre)'), '=', mb_strtolower($producto->nombre))
                ->first();

            if ($medicamento) {
                DB::table('medico_productos')
                    ->where('id', $producto->id)
                    ->update(['medicamento_id' => $medicamento->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('medico_productos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('medicamento_id');
        });
    }
};
