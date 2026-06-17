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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizador_id')->constrained('users')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descricao');
            $table->dateTime('data_evento');
            $table->string('local');
            $table->integer('capacidade_maxima');
            $table->integer('vagas_disponiveis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
