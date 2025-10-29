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
        Schema::create('exemplaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('livre_id')->constrained()->onDelete('cascade');
            $table->string('numero_exemplaire')->unique();
            $table->enum('statut', ['disponible', 'emprunte', 'reserve', 'perdu', 'endommage'])->default('disponible');
            $table->string('localisation')->nullable();
            $table->date('date_acquisition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exemplaires');
    }
};
