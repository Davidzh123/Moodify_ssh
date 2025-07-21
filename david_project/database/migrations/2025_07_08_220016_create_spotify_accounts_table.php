<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table `spotify_accounts`.
     */
    public function up(): void
    {
        Schema::create('spotify_accounts', function (Blueprint $table) {
            $table->id();

            /* --- Relation avec l’utilisateur Laravel --- */
            $table->foreignId('user_id')
                  ->constrained()      // référence users.id
                  ->onDelete('cascade');

            /* --- Infos renvoyées par l’API Spotify --- */
            $table->string('spotify_id')->unique();       // ID interne Spotify
            $table->string('display_name')->nullable();   // pseudo / nom d’affichage
            $table->string('email')->nullable();          // e-mail Spotify (facultatif)
            $table->string('avatar')->nullable();         // URL de la photo de profil
            $table->string('profile_url')->nullable();    // URL du profil public

            /* --- Jetons OAuth --- */
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at')->nullable();  // date d’expiration du token

            $table->timestamps();                         // created_at & updated_at
        });
    }

    /**
     * Supprime la table `spotify_accounts`.
     */
    public function down(): void
    {
        Schema::dropIfExists('spotify_accounts');
    }
};
