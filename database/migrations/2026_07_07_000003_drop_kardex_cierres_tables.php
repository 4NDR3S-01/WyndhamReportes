<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('medico_kardex_cierre_items');
        Schema::dropIfExists('medico_kardex_cierres');
    }

    public function down(): void
    {
        // Las tablas no se recrean — el kardex ahora se calcula en memoria.
        // Para restaurar, usar la migración de rebuild.
    }
};
