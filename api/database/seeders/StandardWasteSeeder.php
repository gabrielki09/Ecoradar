<?php

namespace Database\Seeders;

use App\Models\WasteCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StandardWasteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wasteCategories = [
            [
                'name' => 'Lixo eletrônico',
                'is_system' => true
            ],
            [
                'name' => 'Pilhas e baterias',
                'is_system' => true
            ],
            [
                'name' => 'Óleo de cozinha usado',
                'is_system' => true
            ],
            [
                'name' => 'Vidro',
                'is_system' => true
            ],
            [
                'name' => 'Plástico',
                'is_system' => true
            ],
            [
                'name' => 'Papel e papelão',
                'is_system' => true
            ],
            [
                'name' => 'Metal',
                'is_system' => true
            ],
            [
                'name' => 'Outros materiais recicláveis',
                'is_system' => true
            ],
        ];

        foreach ($wasteCategories as $category) {
            WasteCategory::create($category);
        }
    }
}
