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
        Schema::create('hunter_levels', function (Blueprint $table) {
            $table->unsignedInteger('level')->primary();
            $table->unsignedBigInteger('required_total_xp');
            $table->string('rank_unlocked')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hunter_levels');
    }
};
