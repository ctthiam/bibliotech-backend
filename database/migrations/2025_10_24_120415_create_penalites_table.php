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
        Schema::create('penalites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecteur_id')->constrained()->onDelete('cascade');
            $table->foreignId('emprunt_id')->constrained()->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('motif')->nullable();
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_paiement')->nullable();
            $table->enum('statut', ['impayee', 'payee', 'annulee'])->default('impayee');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalites');
    }
};
