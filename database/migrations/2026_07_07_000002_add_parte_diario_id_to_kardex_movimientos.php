<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_kardex_movimientos', function (Blueprint $table): void {
            $table->foreignId('parte_diario_id')->nullable()->after('producto_id')
                ->constrained('medico_partes_diarios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('medico_kardex_movimientos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parte_diario_id');
        });
    }
};
