<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    /** Laravel treats "food" as uncountable, so the table is named explicitly. */
    protected $table = 'foods';

    protected $fillable = [
        'name', 'slug', 'cuisine', 'category', 'serving_label', 'serving_grams',
        'calories', 'protein_g', 'carbs_g', 'fat_g', 'notes', 'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'serving_grams' => 'integer',
            'calories' => 'integer',
            'protein_g' => 'float',
            'carbs_g' => 'float',
            'fat_g' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Macros for a portion, by servings or by weight.
     *
     * Weight wins when given: "180g of rice" is more precise than "1.2 cups",
     * and a food's serving weight is what converts between the two.
     *
     * @return array{servings: float, calories: int, protein_g: float, carbs_g: float, fat_g: float}
     */
    public function portion(?float $servings = null, ?float $grams = null): array
    {
        $factor = $grams !== null && $this->serving_grams > 0
            ? $grams / $this->serving_grams
            : ($servings ?? 1.0);

        return [
            'servings' => round($factor, 2),
            'calories' => (int) round($this->calories * $factor),
            'protein_g' => round($this->protein_g * $factor, 1),
            'carbs_g' => round($this->carbs_g * $factor, 1),
            'fat_g' => round($this->fat_g * $factor, 1),
        ];
    }
}
