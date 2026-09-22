<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('products')->truncate();
        DB::table('categories')->truncate();
        Schema::enableForeignKeyConstraints();

        $categories = [
            [
                'name' => 'Hamburguesas',
                'icon' => 'burger',
                'description' => 'Burgers de autor con carne 100% Angus, pan brioche artesanal y salsas de la casa.',
            ],
            [
                'name' => 'Pizzas',
                'icon' => 'pizza',
                'description' => 'Masa con fermentación lenta, ingredientes seleccionados y horneada a la piedra.',
            ],
            [
                'name' => 'Kebab',
                'icon' => 'kebab',
                'description' => 'Döner, durum y shawarma con carne marinada y especias mediterráneas de primera calidad.',
            ],
            [
                'name' => 'Carne & Pollo',
                'icon' => 'chicken',
                'description' => 'Alitas, tiras y nuggets de pollo 100% natural. Crujientes por fuera, jugosos por dentro.',
            ],
            [
                'name' => 'Acompañantes',
                'icon' => 'fries',
                'description' => 'Patatas artesanales, aros crujientes y extras imprescindibles para completar tu pedido.',
            ],
            [
                'name' => 'Ensaladas',
                'icon' => 'salad',
                'description' => 'Ensaladas frescas, completas y equilibradas para quienes buscan algo más ligero.',
            ],
            [
                'name' => 'Bebidas',
                'icon' => 'drink',
                'description' => 'Refrescos, zumos y energéticas bien frías para acompañar cualquier pedido.',
            ],
            [
                'name' => 'Salsas',
                'icon' => 'sauce',
                'description' => 'Nuestras salsas artesanales, elaboradas en cocina, para acompañar cualquier plato.',
            ],
            [
                'name' => 'Menús',
                'icon' => 'combo',
                'description' => 'Combos completos con plato principal, acompañante y bebida al mejor precio.',
            ],
        ];

        foreach ($categories as $index => $category) {
            Category::query()->create([
                'name' => $category['name'],
                'slug' => str($category['name'])->slug(),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
