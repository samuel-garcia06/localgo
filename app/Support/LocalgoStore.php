<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LocalgoStore
{
    public const CART_SESSION_KEY = 'localgo_cart';

    public static function catalog(): array
    {
        static $catalog = null;

        if ($catalog !== null) {
            return $catalog;
        }

        $catalog = json_decode(
            file_get_contents(base_path('resources/data/fast-bite-menu.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $catalog;
    }

    public static function restaurant(): array
    {
        return self::catalog()['restaurant'];
    }

    public static function categories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($q) => $q->where('is_available', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => self::categoryIcon($category->icon),
            ]);
    }

    public static function products(): Collection
    {
        return Product::query()
            ->with('category')
            ->where('is_available', true)
            ->whereNotNull('image_path')
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy(
                Category::query()
                    ->select('sort_order')
                    ->whereColumn('categories.id', 'products.category_id')
                    ->limit(1)
            )
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description ?? '',
                'price' => (float) $product->price,
                'image_path' => $product->image_path,
                'image_url' => self::productImageUrl($product->image_path),
                'image' => self::productImageUrl($product->image_path),
                'category' => $product->category?->slug,
                'category_name' => $product->category?->name ?? 'Carta',
                'tag' => $product->category?->name ?? 'Carta',
                'has_drink_option' => (bool) $product->has_drink_option,
                'has_sauce_option' => (bool) $product->has_sauce_option,
                'drink_option_count' => (int) $product->drink_option_count,
                'sauce_option_count' => (int) $product->sauce_option_count,
            ]);
    }

    public static function filteredProducts(?string $selectedCategory, string $search): Collection
    {
        $term = Str::lower(trim($search));

        return self::products()
            ->when($selectedCategory, fn (Collection $products) => $products->where('category', $selectedCategory))
            ->filter(function (array $product) use ($term) {
                if ($term === '') {
                    return true;
                }

                return Str::contains(
                    Str::lower($product['name'].' '.$product['description'].' '.$product['category']),
                    $term
                );
            })
            ->values();
    }

    public static function cartItems(): Collection
    {
        $productIndex = self::products()->keyBy('id');

        return collect(session()->get(self::CART_SESSION_KEY, []))
            ->map(function (int $quantity, int|string $productId) use ($productIndex) {
                $product = $productIndex->get((int) $productId);

                if ($product === null || $quantity <= 0) {
                    return null;
                }

                return [
                    ...$product,
                    'quantity' => $quantity,
                    'line_total' => $product['price'] * $quantity,
                ];
            })
            ->filter()
            ->values();
    }

    public static function cartQuantity(): int
    {
        return (int) self::cartItems()->sum('quantity');
    }

    public static function subtotal(): float
    {
        return (float) self::cartItems()->sum('line_total');
    }

    public static function deliveryFee(string $fulfillment, ?float $subtotal = null): float
    {
        $subtotal ??= self::subtotal();

        if ($subtotal <= 0 || $fulfillment === 'pickup') {
            return 0;
        }

        // Lee el coste de envío desde la configuración del restaurante (DB).
        // Si no hay registro aún, usa el valor del catálogo JSON como fallback.
        $settings = RestaurantSetting::first();
        if ($settings !== null) {
            return (float) $settings->delivery_fee;
        }

        return (float) (self::restaurant()['delivery_fee_value'] ?? 0);
    }

    public static function total(string $fulfillment): float
    {
        $subtotal = self::subtotal();

        return $subtotal + self::deliveryFee($fulfillment, $subtotal);
    }

    public static function placeholderImageUrl(): string
    {
        return 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%221200%22 height=%22825%22 viewBox=%220 0 1200 825%22%3E%3Crect width=%221200%22 height=%22825%22 fill=%22%23eef2f0%22/%3E%3Cpath d=%22M210 570c86-118 142-177 191-177 55 0 87 79 142 79 49 0 82-106 147-106 67 0 121 105 300 204H210Z%22 fill=%22%23cbd8d1%22/%3E%3Ccircle cx=%22884%22 cy=%22225%22 r=%2274%22 fill=%22%23dbe5df%22/%3E%3C/svg%3E';
    }

    private static function productImageUrl(?string $path): string
    {
        if (blank($path)) {
            return self::placeholderImageUrl();
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    public static function categoryIconForKey(?string $iconKey): string
    {
        return self::categoryIcon($iconKey);
    }

    private static function categoryIcon(?string $iconKey): string
    {
        static $map = [
            'pizza' => '🍕',
            'burger' => '🍔',
            'chicken' => '🍗',
            'kebab' => '🥙',
            'fries' => '🍟',
            'wings' => '🍖',
            'salad' => '🥗',
            'drink' => '🥤',
            'sauce' => '🥣',
            'combo' => '🍽️',
            'rice' => '🍚',
            'pasta' => '🍝',
            'sandwich' => '🥪',
            'taco' => '🌮',
            'hotdog' => '🌭',
            'fish' => '🐟',
            'shrimp' => '🍤',
            'sushi' => '🍣',
            'soup' => '🍲',
            'dessert' => '🍰',
            'ice-cream' => '🍦',
            'coffee' => '☕',
            'bakery' => '🥐',
            'bread' => '🍞',
            'snack' => '🍿',
            'chips' => '🥨',
            'offer' => '🏷️',
            'utensils' => '🍴',
        ];

        if ($iconKey && isset($map[$iconKey])) {
            return $map[$iconKey];
        }

        return '🍴';
    }
}
