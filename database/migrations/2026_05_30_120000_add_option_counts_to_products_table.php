<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedTinyInteger('drink_option_count')->default(0)->after('has_sauce_option');
            $table->unsignedTinyInteger('sauce_option_count')->default(0)->after('drink_option_count');
        });

        DB::table('products')
            ->select('id', 'category_id', 'name', 'description', 'has_drink_option', 'has_sauce_option')
            ->orderBy('id')
            ->chunkById(100, function ($products): void {
                $categoryNames = DB::table('categories')
                    ->pluck('name', 'id');

                foreach ($products as $product) {
                    $category = (string) ($categoryNames[$product->category_id] ?? '');
                    $drinkCount = $this->extractOptionCount($category, (string) $product->name, (string) $product->description, 'bebida');
                    $sauceCount = $this->extractOptionCount($category, (string) $product->name, (string) $product->description, 'salsa');

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'has_drink_option' => $drinkCount > 0,
                            'has_sauce_option' => $sauceCount > 0,
                            'drink_option_count' => $drinkCount,
                            'sauce_option_count' => $sauceCount,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['drink_option_count', 'sauce_option_count']);
        });
    }

    private function extractOptionCount(string $category, string $name, string $description, string $keyword): int
    {
        $normalizedCategory = str($category)->lower()->value();

        if (($keyword === 'bebida' && $normalizedCategory === 'bebidas')
            || ($keyword === 'salsa' && $normalizedCategory === 'salsas')) {
            return 0;
        }

        $text = strtolower(trim($name.' '.$description));

        if (! str_contains($text, $keyword)) {
            return 0;
        }

        if (preg_match('/(\d+)\s+'.preg_quote($keyword, '/').'s?\b/u', $text, $matches) === 1) {
            return max((int) $matches[1], 0);
        }

        return 1;
    }
};
