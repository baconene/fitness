<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Reference foods. Macros are stored per serving rather than per 100g:
         * people plan in servings ("one cup of rice"), and a serving also
         * carries its weight so a gram-based portion can still be derived.
         */
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('cuisine', 40)->default('filipino');
            $table->string('category', 40);
            $table->string('serving_label', 60);
            $table->unsignedSmallInteger('serving_grams');
            $table->unsignedSmallInteger('calories');
            $table->decimal('protein_g', 5, 1);
            $table->decimal('carbs_g', 5, 1);
            $table->decimal('fat_g', 5, 1);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['cuisine', 'category']);
        });

        // One plan per hunter per day.
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('plan_date');
            $table->timestamp('logged_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'plan_date']);
        });

        /*
         * Macros are copied onto the item rather than read through the food.
         * A plan is a record of what was intended at the time, so correcting a
         * food's data later must not silently rewrite yesterday's plan.
         */
        Schema::create('meal_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_id')->constrained('meal_plans')->cascadeOnDelete();
            $table->foreignId('food_id')->nullable()->constrained('foods')->nullOnDelete();
            $table->string('meal', 20);
            $table->string('name', 120);
            $table->decimal('servings', 5, 2)->default(1);
            $table->unsignedSmallInteger('calories');
            $table->decimal('protein_g', 6, 1);
            $table->decimal('carbs_g', 6, 1);
            $table->decimal('fat_g', 6, 1);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['meal_plan_id', 'meal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plan_items');
        Schema::dropIfExists('meal_plans');
        Schema::dropIfExists('foods');
    }
};
