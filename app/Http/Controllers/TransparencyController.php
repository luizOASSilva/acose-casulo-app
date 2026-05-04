<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentCategory;

class TransparencyController extends Controller
{
    public function index()
    {
        $year = request('year');

        if (!$year) {
            $year = Document::max('year') ?? now()->year;
        }

        $years = Document::select('year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year');

        $categories = DocumentCategory::with([
            'documents' => fn ($q) =>
                $q->where('year', $year)->orderBy('title')
        ])
        ->orderBy('order')
        ->get()
        ->map(function ($category) {
            $category->documents = $category->documents->values();
            return $category;
        })
        ->filter(fn ($c) => $c->documents->isNotEmpty())
        ->values();

        $featured = $categories->firstWhere('featured', true);

        return response()->json([
            'year' => (int) $year,
            'years' => $years,
            'categories' => $categories,
            'featured' => $featured,
        ]);
    }
}
