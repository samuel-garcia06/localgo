<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'slug' => Str::slug(fake()->unique()->words(3, true)),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 30),
            'image_path' => 'products/'.fake()->uuid().'.jpg',
            'is_available' => true,
        ];
    }
}
