<?php

namespace Database\Factories;

use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            [
                'name' => 'Technology',
                'description' => 'Posts about technology, software development, and digital innovations'
            ],
            [
                'name' => 'Travel',
                'description' => 'Adventures, travel tips, and destination guides'
            ],
            [
                'name' => 'Lifestyle',
                'description' => 'Personal development, wellness, and daily life experiences'
            ],
            [
                'name' => 'Food & Cooking',
                'description' => 'Recipes, cooking tips, and food culture from around the world'
            ],
            [
                'name' => 'Business',
                'description' => 'Entrepreneurship, career advice, and business strategies'
            ],
            [
                'name' => 'Art & Culture',
                'description' => 'Art, music, literature, and cultural discussions'
            ]
        ];

        $category = fake()->unique()->randomElement($categories);
        
        return [
            'name' => $category['name'],
            'description' => $category['description'],
            'slug' => Str::slug($category['name'])
        ];
    }
}
