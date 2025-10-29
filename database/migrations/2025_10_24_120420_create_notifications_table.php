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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destinataire_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['rappel', 'retard', 'disponibilite', 'information']);
            $table->string('titre');
            $table->text('contenu');
            $table->timestamp('date_envoi')->useCurrent();
            $table->enum('statut', ['envoyee', 'lue'])->default('envoyee');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
