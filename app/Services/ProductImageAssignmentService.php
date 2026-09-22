<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProductImageAssignmentService
{
    private const DIRECTORY = 'products/catalog';

    private const FALLBACK_SOURCE = [
        'name' => 'generic-food',
        'category' => 'generic',
        'url' => 'https://images.unsplash.com/photo-1512152272829-e3139592d56f?auto=format&fit=crop&w=1400&q=85',
    ];

    private const EXTRA_SOURCES = [
        ['name' => 'generic-burger', 'category' => 'hamburguesas', 'url' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-pizza', 'category' => 'pizzas', 'url' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-chicken', 'category' => 'pollos', 'url' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-fries', 'category' => 'patatas-y-entrantes', 'url' => 'https://images.unsplash.com/photo-1576107232684-1279f390859f?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-drinks', 'category' => 'bebidas', 'url' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-dessert', 'category' => 'postres', 'url' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-sandwich', 'category' => 'bocadillos', 'url' => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-nachos', 'category' => 'complementos', 'url' => 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?auto=format&fit=crop&w=1400&q=85'],
        ['name' => 'generic-salad', 'category' => 'ensaladas', 'url' => 'https://images.unsplash.com/photo-1482049016688-2d3e1b311543?auto=format&fit=crop&w=1400&q=85'],
    ];

    public function assign(bool $force = false, ?Command $command = null, bool $downloadMissing = false): array
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory(self::DIRECTORY);

        $sources = $this->sources();
        $downloaded = $this->downloadSources($sources, $command, $downloadMissing);
        $fallback = $downloaded->firstWhere('name', self::FALLBACK_SOURCE['name']) ?? $downloaded->first();

        $updated = 0;
        $skipped = 0;

        Product::query()
            ->with('category')
            ->orderBy('id')
            ->chunkById(100, function (Collection $products) use ($downloaded, $fallback, $force, &$updated, &$skipped): void {
                foreach ($products as $product) {
                    if (! $force && filled($product->image_path) && Storage::disk('public')->exists($product->image_path)) {
                        $skipped++;

                        continue;
                    }

                    $asset = $this->matchAsset($product, $downloaded) ?? $fallback;

                    if (! $asset) {
                        $skipped++;

                        continue;
                    }

                    $product->forceFill(['image_path' => $asset['path']])->save();
                    $updated++;
                }
            });

        return [
            'downloaded' => $downloaded->count(),
            'updated' => $updated,
            'skipped' => $skipped,
            'missing' => $this->missingProducts()->count(),
        ];
    }

    public function missingProducts(): Collection
    {
        return Product::query()
            ->orderBy('id')
            ->get()
            ->filter(fn (Product $product) => blank($product->image_path) || ! Storage::disk('public')->exists($product->image_path))
            ->values();
    }

    public function sourceSummary(): array
    {
        return [
            'project_json' => 'resources/data/fast-bite-menu.json',
            'downloaded_to' => 'storage/app/public/'.self::DIRECTORY,
            'public_url_prefix' => '/storage/'.self::DIRECTORY,
        ];
    }

    private function sources(): Collection
    {
        $jsonSources = collect(json_decode(file_get_contents(base_path('resources/data/fast-bite-menu.json')), true, 512, JSON_THROW_ON_ERROR)['products'] ?? [])
            ->filter(fn (array $product) => filled($product['image'] ?? null))
            ->map(fn (array $product) => [
                'name' => $product['name'],
                'category' => $product['category'] ?? 'generic',
                'url' => $product['image'],
            ]);

        return $jsonSources
            ->merge(self::EXTRA_SOURCES)
            ->push(self::FALLBACK_SOURCE)
            ->unique(fn (array $source) => $this->assetName($source))
            ->values();
    }

    private function downloadSources(Collection $sources, ?Command $command, bool $downloadMissing): Collection
    {
        return $sources
            ->map(function (array $source) use ($command, $downloadMissing): ?array {
                $filename = $this->assetName($source).'.jpg';
                $path = self::DIRECTORY.'/'.$filename;

                if (! Storage::disk('public')->exists($path)) {
                    if (! $downloadMissing) {
                        return null;
                    }

                    try {
                        $response = Http::timeout(30)->retry(2, 400, throw: false)->get($source['url']);
                    } catch (Throwable $exception) {
                        $command?->warn("No se pudo descargar {$source['name']}: {$exception->getMessage()}");

                        return null;
                    }

                    if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                        $command?->warn("No se pudo descargar {$source['name']}");

                        return null;
                    }

                    Storage::disk('public')->put($path, $response->body());
                }

                return [
                    ...$source,
                    'path' => $path,
                    'keywords' => $this->keywords($source),
                ];
            })
            ->filter()
            ->values();
    }

    private function matchAsset(Product $product, Collection $assets): ?array
    {
        $haystack = Str::of($product->name.' '.$product->description.' '.$product->category?->name.' '.$product->category?->slug)
            ->ascii()
            ->lower()
            ->value();

        $category = $this->categoryAlias($product->category?->slug);

        $exact = $assets->first(function (array $asset) use ($haystack): bool {
            $name = Str::of($asset['name'])->ascii()->lower()->value();

            return Str::contains($haystack, $name) || Str::contains($name, Str::of($haystack)->words(2, '')->value());
        });

        if ($exact) {
            return $exact;
        }

        $keyword = $assets->first(fn (array $asset) => collect($asset['keywords'])->contains(fn (string $keyword) => Str::contains($haystack, $keyword)));

        if ($keyword) {
            return $keyword;
        }

        $categoryAssets = $assets->where('category', $category)->values();

        if ($categoryAssets->isNotEmpty()) {
            return $categoryAssets[abs(crc32($product->slug)) % $categoryAssets->count()];
        }

        return $assets->firstWhere('category', 'generic');
    }

    private function keywords(array $source): array
    {
        $text = Str::of($source['name'].' '.$source['category'])->ascii()->lower()->value();

        return collect(preg_split('/[^a-z0-9]+/', $text) ?: [])
            ->filter(fn (string $word) => strlen($word) >= 4)
            ->values()
            ->all();
    }

    private function assetName(array $source): string
    {
        return Str::slug(($source['category'] ?? 'generic').'-'.$source['name']);
    }

    private function categoryAlias(?string $category): string
    {
        return match ($category) {
            'pollos' => 'pollo',
            'complementos', 'patatas-gratinadas' => 'patatas-y-entrantes',
            'kebab-y-shawarma' => 'bocadillos',
            'arroces', 'salsas' => 'complementos',
            'ofertas' => 'menus',
            default => $category ?: 'generic',
        };
    }
}
