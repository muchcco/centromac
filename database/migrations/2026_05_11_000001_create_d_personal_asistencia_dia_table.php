<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('d_personal_asistencia_dia')) {
            return;
        }

        Schema::create('d_personal_asistencia_dia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_asignacion');
            $table->date('fecha');
            $table->string('hora_ingreso', 50);
            $table->string('hora_salida', 50);
            $table->tinyInteger('activo')->default(1);
            $table->timestamps();

            $table->unique(['id_asignacion', 'fecha'], 'uniq_asistencia_dia');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('d_personal_asistencia_dia');
    }
};
