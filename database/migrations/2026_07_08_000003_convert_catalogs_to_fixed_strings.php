<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medico_partes_diarios', function (Blueprint $table): void {
            // Nuevas columnas de texto
            $table->string('tipo_certificado')->nullable()->after('entidad_certificado_id');
            $table->string('tipo_salida')->nullable()->after('tipo_salida_id');
            $table->string('incidente')->nullable()->after('incidente_id');
        });

        // Migrar datos existentes de FK a texto
        DB::statement('
            UPDATE medico_partes_diarios
            SET tipo_certificado = (SELECT nombre FROM tipos_certificado WHERE id = medico_partes_diarios.tipo_certificado_id)
            WHERE tipo_certificado_id IS NOT NULL
        ');

        DB::statement('
            UPDATE medico_partes_diarios
            SET tipo_salida = (SELECT nombre FROM tipos_salida WHERE id = medico_partes_diarios.tipo_salida_id)
            WHERE tipo_salida_id IS NOT NULL
        ');

        DB::statement('
            UPDATE medico_partes_diarios
            SET incidente = (SELECT nombre FROM incidentes WHERE id = medico_partes_diarios.incidente_id)
            WHERE incidente_id IS NOT NULL
        ');

        // Eliminar las columnas FK antiguas
        Schema::table('medico_partes_diarios', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('tipo_certificado_id');
            $table->dropConstrainedForeignId('tipo_salida_id');
            $table->dropConstrainedForeignId('incidente_id');
        });
    }

    public function down(): void
    {
        // No reversible — los datos ya están en texto
    }
};
