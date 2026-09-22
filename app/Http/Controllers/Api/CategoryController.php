<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Category::query()
                ->select(['id', 'name', 'slug', 'description', 'sort_order'])
                ->where('is_active', true)
                ->withCount(['products' => fn ($query) => $query->where('is_available', true)])
                ->orderBy('sort_order')
                ->get()
        );
    }
}
