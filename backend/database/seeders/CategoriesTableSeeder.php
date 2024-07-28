<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Politics',
            'Sports',
            'Technology',
            'Health',
            'Business',
            // Add more categories as needed
        ];

        foreach ($categories as $category) {
            Category::create(['name' => $category]);
        }
    }
}
