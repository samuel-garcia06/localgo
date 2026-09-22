<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Support\LocalgoStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StorefrontCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_catalog_uses_database_products_and_categories(): void
    {
        $category = Category::factory()->create([
            'name' => 'Demo',
            'slug' => 'demo',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Producto visible',
            'slug' => 'producto-visible',
            'price' => 12.50,
            'image_path' => 'products/demo.jpg',
            'is_available' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Producto oculto',
            'slug' => 'producto-oculto',
            'is_available' => false,
        ]);

        $this->assertSame(['demo'], LocalgoStore::categories()->pluck('slug')->all());
        $this->assertSame(['Producto visible'], LocalgoStore::products()->pluck('name')->all());
        $this->assertSame(12.50, LocalgoStore::products()->first()['price']);
        $this->assertStringContainsString('/storage/products/demo.jpg', LocalgoStore::products()->first()['image']);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Category::factory()
            ->has(Product::factory())
            ->create();

        $this->expectException(ValidationException::class);

        $category->delete();
    }
}
