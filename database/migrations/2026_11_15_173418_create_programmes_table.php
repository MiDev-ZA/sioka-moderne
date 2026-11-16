<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('day', 10);          // Lundi … Dimanche
            $table->time('start', 0);   // HH:MM:SS sans fraction
            $table->time('end', 0);
            $table->string('title', 180);
            $table->string('description', 200);
            $table->timestamps();

            $table->index(['day', 'start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};