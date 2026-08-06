<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentCategoryResource;
use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransparencyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $year = $request->input('year');

        if (! $year) {
            $year = Document::query()->max('year') ?? now()->year;
        }

        $year = (int) $year;

        $years = Document::query()
            ->select('year')
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($item) => (int) $item)
            ->values();

        $categories = DocumentCategory::query()
            ->with([
                'documents' => fn ($query) => $query
                    ->where('year', $year)
                    ->with('category')
                    ->orderBy('title'),
            ])
            ->orderBy('order')
            ->get()
            ->map(function (DocumentCategory $category) {
                $category->setRelation(
                    'documents',
                    $category->documents->values()
                );

                return $category;
            })
            ->filter(
                fn (DocumentCategory $category) =>
                    $category->documents->isNotEmpty()
            )
            ->values();

        $featured = $categories->firstWhere('featured', true);

        return response()->json([
            'year' => $year,
            'years' => $years,

            'categories' => DocumentCategoryResource::collection(
                $categories
            )->resolve($request),

            'featured' => $featured
                ? DocumentCategoryResource::make($featured)
                    ->resolve($request)
                : null,
        ]);
    }
}
