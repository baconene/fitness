<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

/**
 * A reference table of Filipino dishes and staples.
 *
 * Figures are per the stated household serving, which is how people actually
 * portion these — a cup of rice, a piece of longganisa — with the serving
 * weight recorded alongside so a gram-based portion can still be derived.
 *
 * These are representative averages for planning. Recipes vary enormously
 * between households, so treat them as a starting point rather than a
 * laboratory analysis.
 */
class FilipinoFoodSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->foods() as $row) {
            [$category, $name, $servingLabel, $grams, $calories, $protein, $carbs, $fat] = $row;

            Food::updateOrCreate(
                ['slug' => str($name)->slug()->value()],
                [
                    'name' => $name,
                    'cuisine' => 'filipino',
                    'category' => $category,
                    'serving_label' => $servingLabel,
                    'serving_grams' => $grams,
                    'calories' => $calories,
                    'protein_g' => $protein,
                    'carbs_g' => $carbs,
                    'fat_g' => $fat,
                    'notes' => $row[8] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * [category, name, serving label, grams, kcal, protein, carbs, fat, notes?]
     *
     * @return array<int, array<int, mixed>>
     */
    private function foods(): array
    {
        return [
            // ---- Rice and grains ----
            ['grain', 'Steamed White Rice', '1 cup cooked', 158, 205, 4.3, 44.5, 0.4],
            ['grain', 'Garlic Fried Rice (Sinangag)', '1 cup', 160, 320, 5.5, 47.0, 11.0],
            ['grain', 'Brown Rice', '1 cup cooked', 195, 216, 5.0, 45.0, 1.8],
            ['grain', 'Pandesal', '2 pieces', 60, 180, 5.0, 33.0, 3.0],
            ['grain', 'Puto', '2 pieces', 70, 170, 3.0, 34.0, 2.5],
            ['grain', 'Bibingka', '1 slice', 100, 250, 5.0, 38.0, 9.0],
            ['grain', 'Champorado', '1 bowl', 250, 300, 6.0, 58.0, 5.0],
            ['grain', 'Arroz Caldo', '1 bowl', 300, 290, 14.0, 40.0, 8.0],
            ['grain', 'Lugaw', '1 bowl', 300, 180, 4.0, 38.0, 1.0],

            // ---- Pork ----
            ['pork', 'Pork Adobo', '1 cup', 180, 430, 26.0, 6.0, 33.0, 'Simmered in soy sauce and vinegar; fat varies with the cut.'],
            ['pork', 'Lechon Kawali', '100 g', 100, 450, 20.0, 0.0, 41.0],
            ['pork', 'Pork Sisig', '1 serving', 150, 380, 24.0, 5.0, 29.0],
            ['pork', 'Pork Barbecue Skewer', '1 stick', 90, 190, 15.0, 9.0, 10.0],
            ['pork', 'Inihaw na Liempo', '1 piece', 120, 380, 22.0, 2.0, 31.0],
            ['pork', 'Bicol Express', '1 cup', 180, 400, 18.0, 8.0, 33.0],
            ['pork', 'Menudo', '1 cup', 200, 320, 20.0, 18.0, 19.0],
            ['pork', 'Dinuguan', '1 cup', 200, 320, 22.0, 6.0, 23.0],
            ['pork', 'Longganisa', '2 pieces', 90, 280, 13.0, 10.0, 21.0],
            ['pork', 'Tocino', '100 g', 100, 280, 17.0, 16.0, 16.0],
            ['pork', 'Crispy Pata', '150 g', 150, 560, 30.0, 1.0, 48.0],
            ['pork', 'Pork Sinigang', '1 bowl', 350, 280, 20.0, 12.0, 17.0],

            // ---- Chicken ----
            ['chicken', 'Chicken Adobo', '1 cup', 180, 350, 30.0, 5.0, 23.0],
            ['chicken', 'Chicken Inasal', '1 piece', 150, 320, 30.0, 4.0, 20.0],
            ['chicken', 'Chicken Tinola', '1 bowl', 350, 240, 26.0, 10.0, 11.0],
            ['chicken', 'Fried Chicken', '1 piece', 120, 320, 24.0, 10.0, 20.0],
            ['chicken', 'Chicken Afritada', '1 cup', 200, 300, 24.0, 16.0, 16.0],
            ['chicken', 'Chicken Curry', '1 cup', 200, 330, 25.0, 12.0, 21.0],
            ['chicken', 'Grilled Chicken Breast', '150 g', 150, 250, 46.0, 0.0, 6.0],

            // ---- Beef ----
            ['beef', 'Beef Caldereta', '1 cup', 200, 380, 26.0, 14.0, 24.0],
            ['beef', 'Bulalo', '1 bowl', 400, 340, 28.0, 10.0, 21.0],
            ['beef', 'Beef Tapa', '100 g', 100, 250, 26.0, 8.0, 12.0],
            ['beef', 'Kare-Kare', '1 cup', 220, 420, 22.0, 16.0, 30.0, 'Peanut sauce; usually served with bagoong which adds sodium.'],
            ['beef', 'Beef Mechado', '1 cup', 200, 350, 26.0, 13.0, 21.0],
            ['beef', 'Sizzling Beef Sisig', '1 serving', 150, 360, 26.0, 5.0, 26.0],

            // ---- Fish and seafood ----
            ['seafood', 'Daing na Bangus', '1 fillet', 150, 290, 28.0, 2.0, 19.0],
            ['seafood', 'Inihaw na Bangus', '1 fillet', 150, 260, 30.0, 1.0, 15.0],
            ['seafood', 'Sinigang na Hipon', '1 bowl', 350, 190, 22.0, 12.0, 6.0],
            ['seafood', 'Ginataang Tilapia', '1 fillet', 180, 300, 26.0, 7.0, 19.0],
            ['seafood', 'Fried Galunggong', '1 piece', 120, 230, 24.0, 0.0, 15.0],
            ['seafood', 'Kinilaw na Tanigue', '1 serving', 150, 180, 26.0, 4.0, 6.0],
            ['seafood', 'Sardinas (canned in tomato)', '1 can', 155, 250, 22.0, 6.0, 15.0],
            ['seafood', 'Tuyo (dried fish)', '2 pieces', 50, 160, 20.0, 0.0, 9.0],

            // ---- Vegetables ----
            ['vegetable', 'Pinakbet', '1 cup', 180, 160, 7.0, 14.0, 9.0],
            ['vegetable', 'Laing', '1 cup', 180, 280, 6.0, 10.0, 24.0],
            ['vegetable', 'Ginataang Gulay', '1 cup', 180, 210, 5.0, 13.0, 16.0],
            ['vegetable', 'Chopsuey', '1 cup', 200, 150, 9.0, 14.0, 7.0],
            ['vegetable', 'Monggo Guisado', '1 cup', 220, 210, 13.0, 27.0, 6.0],
            ['vegetable', 'Ensaladang Talong', '1 serving', 150, 90, 2.0, 10.0, 5.0],
            ['vegetable', 'Adobong Kangkong', '1 cup', 150, 120, 4.0, 9.0, 8.0],
            ['vegetable', 'Lumpiang Gulay', '2 pieces', 100, 200, 4.0, 22.0, 11.0],

            // ---- Noodles ----
            ['noodle', 'Pancit Canton', '1 cup', 200, 320, 12.0, 42.0, 11.0],
            ['noodle', 'Pancit Bihon', '1 cup', 200, 280, 10.0, 41.0, 8.0],
            ['noodle', 'Pancit Palabok', '1 serving', 250, 400, 16.0, 52.0, 14.0],
            ['noodle', 'Lomi', '1 bowl', 350, 380, 18.0, 48.0, 13.0],
            ['noodle', 'Sotanghon Soup', '1 bowl', 300, 220, 12.0, 32.0, 5.0],

            // ---- Snacks and street food ----
            ['snack', 'Lumpiang Shanghai', '3 pieces', 90, 230, 10.0, 18.0, 13.0],
            ['snack', 'Turon', '1 piece', 90, 230, 2.0, 40.0, 8.0],
            ['snack', 'Banana Cue', '1 stick', 100, 210, 1.0, 46.0, 4.0],
            ['snack', 'Kwek-Kwek', '3 pieces', 90, 230, 9.0, 18.0, 14.0],
            ['snack', 'Fish Ball', '5 pieces', 75, 160, 7.0, 18.0, 7.0],
            ['snack', 'Taho', '1 cup', 250, 180, 9.0, 28.0, 4.0],
            ['snack', 'Boiled Saba Banana', '1 piece', 110, 120, 1.3, 31.0, 0.4],
            ['snack', 'Peanuts (adobong mani)', '1/4 cup', 35, 200, 9.0, 6.0, 17.0],

            // ---- Sweets ----
            ['dessert', 'Halo-Halo', '1 glass', 350, 330, 7.0, 60.0, 8.0],
            ['dessert', 'Leche Flan', '1 slice', 100, 300, 7.0, 40.0, 12.0],
            ['dessert', 'Biko', '1 slice', 100, 280, 3.0, 50.0, 8.0],
            ['dessert', 'Ube Halaya', '1/2 cup', 120, 300, 4.0, 48.0, 11.0],
            ['dessert', 'Buko Pandan', '1 cup', 200, 250, 3.0, 35.0, 11.0],
            ['dessert', 'Mango Float', '1 slice', 120, 320, 4.0, 44.0, 15.0],

            // ---- Drinks ----
            ['drink', 'Kapeng Barako (black)', '1 cup', 240, 5, 0.3, 0.0, 0.0],
            ['drink', 'Milk Tea', '1 regular', 350, 280, 3.0, 52.0, 7.0],
            ['drink', 'Buko Juice', '1 glass', 250, 110, 1.0, 26.0, 0.5],
            ['drink', 'Calamansi Juice', '1 glass', 250, 90, 0.3, 23.0, 0.1],
            ['drink', 'Sago at Gulaman', '1 glass', 300, 220, 0.5, 55.0, 0.2],

            // ---- Everyday staples ----
            ['staple', 'Fried Egg', '1 large', 50, 90, 6.3, 0.4, 7.0],
            ['staple', 'Boiled Egg', '1 large', 50, 78, 6.3, 0.6, 5.3],
            ['staple', 'Corned Beef', '1/2 cup', 120, 250, 18.0, 4.0, 18.0],
            ['staple', 'Spam / Luncheon Meat', '2 slices', 56, 180, 7.0, 2.0, 16.0],
            ['staple', 'Instant Noodles', '1 pack', 85, 380, 8.0, 52.0, 15.0],
            ['staple', 'Whole Milk', '1 glass', 250, 150, 8.0, 12.0, 8.0],
            ['staple', 'Whey Protein Scoop', '1 scoop', 30, 120, 24.0, 3.0, 1.5],
        ];
    }
}
