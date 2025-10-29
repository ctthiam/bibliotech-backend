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
        Schema::create('livres', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->string('auteur');
            $table->string('isbn')->unique();
            $table->string('editeur')->nullable();
            $table->integer('annee_publication')->nullable();
            $table->integer('nombre_pages')->nullable();
            $table->string('langue')->default('français');
            $table->foreignId('categorie_id')->nullable()->constrained()->onDelete('set null');
            $table->text('resume')->nullable();
            $table->string('image_couverture')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livres');
    }
};
