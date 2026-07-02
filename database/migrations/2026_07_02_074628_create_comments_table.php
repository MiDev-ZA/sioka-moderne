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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // Lié obligatoirement à un article. Si l'article est supprimé, les commentaires aussi.
            $table->foreignId('article_id')->constrained()->onDelete('cascade');

            // Lié optionnellement à un utilisateur. Si le compte est supprimé, le commentaire reste anonymisé.
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // Nom de l'auteur (Sera utilisé si l'user_id est NULL, pour permettre aux invités d'écrire)
            $table->string('author_name')->nullable();

            $table->text('content');
            $table->boolean('is_approved')->default(false); // Système de modération anti-spam
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};