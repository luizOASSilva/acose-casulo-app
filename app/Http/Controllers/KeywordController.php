<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keyword\StoreKeywordRequest;
use App\Http\Requests\Keyword\UpdateKeywordRequest;
use App\Http\Resources\KeywordResource;
use App\Models\Keyword;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function index(Request $request)
    {
        $query = Keyword::query()
            ->orderBy('word');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->where(
                'word',
                'like',
                "%{$search}%"
            );
        }

        if ($request->boolean('all', true)) {
            return KeywordResource::collection(
                $query->get()
            );
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        return KeywordResource::collection(
            $query
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function store(StoreKeywordRequest $request)
    {
        $validated = $request->validated();

        $keyword = Keyword::firstOrCreate([
            'word' => trim($validated['word']),
        ]);

        return KeywordResource::make(
            $keyword->fresh()
        )
            ->response()
            ->setStatusCode(201);
    }

    public function show(Keyword $keyword)
    {
        return KeywordResource::make($keyword);
    }

    public function update(
        UpdateKeywordRequest $request,
        Keyword $keyword
    ) {
        $validated = $request->validated();

        $keyword->update([
            'word' => trim($validated['word']),
        ]);

        $keyword->refresh();

        return KeywordResource::make($keyword);
    }

    public function destroy(Keyword $keyword)
    {
        $keyword->delete();

        return response()->json(null, 204);
    }
}
