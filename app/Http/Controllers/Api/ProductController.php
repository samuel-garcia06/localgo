<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['nullable', 'integer', 'min:1', 'exists:categories,id'],
        ]);

        $products = Product::query()
            ->select([
                'id',
                'category_id',
                'name',
                'slug',
                'description',
                'price',
                'image_path',
                'has_drink_option',
                'has_sauce_option',
                'drink_option_count',
                'sauce_option_count',
            ])
            ->where('is_available', true)
            ->when($validated['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }
}
