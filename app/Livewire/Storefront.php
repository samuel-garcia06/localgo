<?php

namespace App\Livewire;

use App\Models\RestaurantSetting;
use App\Support\InputSanitizer;
use App\Support\LocalgoStore;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Storefront extends Component
{
    public ?string $selectedCategory = null;

    public string $search = '';

    public string $fulfillment = 'delivery';

    public function selectCategory(?string $category = null): void
    {
        $this->selectedCategory = blank($category) ? null : $category;
    }

    public function setFulfillment(string $mode): void
    {
        if (! in_array($mode, ['delivery', 'pickup'], true)) {
            return;
        }

        $this->fulfillment = $mode;
        session()->put('localgo_fulfillment', $mode);
    }

    public function updatedSearch(string $value): void
    {
        $this->search = InputSanitizer::text($value, 80) ?? '';
    }

    public function mount(): void
    {
        $this->fulfillment = session('localgo_fulfillment', 'delivery');
    }

    public function addToCart(int $productId): void
    {
        $product = LocalgoStore::products()->firstWhere('id', $productId);

        abort_if($product === null, 404);

        $cart = session()->get(LocalgoStore::CART_SESSION_KEY, []);
        $cart[$productId] = (int) ($cart[$productId] ?? 0) + 1;

        session()->put(LocalgoStore::CART_SESSION_KEY, $cart);

        $this->dispatch(
            'product-added',
            productId: $productId,
            productName: $product['name'],
            cartTotal: number_format(LocalgoStore::total($this->fulfillment), 2, ',', '.').' €',
        );
    }

    public function render(): View
    {
        $restaurant = LocalgoStore::restaurant();
        $categories = LocalgoStore::categories();
        $products = LocalgoStore::filteredProducts($this->selectedCategory, $this->search);
        $cartQuantity = LocalgoStore::cartQuantity();
        $total = LocalgoStore::total($this->fulfillment);

        $groupedProducts = $categories
            ->map(function (array $category) use ($products) {
                $items = $products->where('category', $category['slug'])->values();

                return [
                    'category' => [...$category, 'count' => $items->count()],
                    'products' => $items,
                ];
            })
            ->filter(fn (array $group) => $group['products']->isNotEmpty())
            ->values();

        $restaurantSettings = RestaurantSetting::current();

        return view('livewire.storefront', [
            'restaurant' => $restaurant,
            'categories' => $categories,
            'groupedProducts' => $groupedProducts,
            'cartQuantity' => $cartQuantity,
            'cartTotal' => $total,
            'selectedCategoryName' => $categories->firstWhere('slug', $this->selectedCategory)['name'] ?? 'Todo el menú',
            'productsCount' => $products->count(),
            'cartUrl' => route('cart'),
            'homeUrl' => route('home'),
            'currentRoute' => 'home',
            'headerTitle' => 'Pide rápido',
            'headerSubtitle' => 'Entrega o recogida en pocos pasos.',
            'restaurantSettings' => $restaurantSettings,
        ])->layout('layouts.app', [
            'title' => 'Urban Bites | Pedidos online',
        ]);
    }
}
