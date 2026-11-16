<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boites', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 40);
            $table->text('message');
            $table->string('name', 120)->nullable();
            $table->string('contact', 120)->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boites');
    }
};