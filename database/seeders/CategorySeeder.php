<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'À la une',
            'Politique',
            'Société',
            'Économie',
            'Environnement',
            'Santé',
            'Éducation',
            'Culture',
            'Sport',
            'Religion',
            'International',
            'Technologie',
            'Faits divers',
            'Événements',
            'Opinions',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
        }
    }
}