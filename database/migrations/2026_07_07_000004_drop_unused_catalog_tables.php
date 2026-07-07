<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cirugias_generales');
        Schema::dropIfExists('flebologias_vasculares');
        Schema::dropIfExists('atenciones_medicas');
    }

    public function down(): void
    {
        // Catálogos huérfanos — no se recrean.
        // Si se necesita restaurar, usar la migración de rebuild.
    }
};
