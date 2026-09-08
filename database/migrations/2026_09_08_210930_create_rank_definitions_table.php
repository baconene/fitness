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
        Schema::create('rank_definitions', function (Blueprint $table) {
            $table->string('rank')->primary();
            $table->unsignedInteger('min_level');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rank_definitions');
    }
};
