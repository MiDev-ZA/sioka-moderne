<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_tokens', function (Blueprint $table) {
            $table->text('access_token')->change();
            $table->text('refresh_token')->nullable()->change();
        });

        // Les anciens tokens étaient en clair : illisibles avec le chiffrement.
        DB::table('youtube_tokens')->delete();
    }

    public function down(): void
    {
        Schema::table('youtube_tokens', function (Blueprint $table) {
            $table->string('access_token')->change();
            $table->string('refresh_token')->nullable()->change();
        });
    }
};