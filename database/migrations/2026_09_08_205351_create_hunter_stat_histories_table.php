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
        Schema::create('hunter_stat_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hunter_profile_id')->constrained('hunter_profiles')->cascadeOnDelete();
            $table->string('stat');
            $table->unsignedSmallInteger('previous_value');
            $table->unsignedSmallInteger('new_value');
            $table->integer('delta');
            $table->string('reason');
            $table->nullableMorphs('source');
            $table->timestamps();

            $table->index(['hunter_profile_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hunter_stat_histories');
    }
};
