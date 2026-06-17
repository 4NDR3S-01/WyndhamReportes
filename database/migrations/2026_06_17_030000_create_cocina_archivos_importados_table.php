<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cocina_archivos_importados', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_original');
            $table->string('nombre_guardado');
            $table->string('ruta')->unique();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('tamano_bytes')->default(0);
            $table->string('estado')->default('recibido');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha_subida')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cocina_archivos_importados');
    }
};
