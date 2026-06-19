<?php

namespace App\Http\Controllers;

use App\Http\Requests\Keyword\StoreKeywordRequest;
use App\Http\Requests\Keyword\UpdateKeywordRequest;
use App\Http\Resources\KeywordResource;
use App\Models\Keyword;
use App\Models\KeywordTranslation;
use App\Support\TranslationDispatcher;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function index(Request $request)
    {
        $query = Keyword::query()
            ->with('translations')
            ->orderBy('word');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->where(function ($keywordQuery) use ($search) {
                $keywordQuery
                    ->where('word', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($translationQuery) use ($search) {
                        $translationQuery->where('word', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->boolean('all', true)) {
            return KeywordResource::collection($query->get());
        }

        $perPage = (int) $request->input('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        return KeywordResource::collection(
            $query->paginate($perPage)->withQueryString()
        );
    }

    public function store(StoreKeywordRequest $request)
    {
        $validated = $request->validated();

        $keyword = Keyword::firstOrCreate([
            'word' => trim($validated['word']),
        ]);

        $this->syncPortugueseTranslation($keyword);

        TranslationDispatcher::keyword($keyword);

        return KeywordResource::make(
            $keyword->fresh('translations')
        )->response()->setStatusCode(201);
    }

    public function show(Keyword $keyword)
    {
        return KeywordResource::make(
            $keyword->load('translations')
        );
    }

    public function update(UpdateKeywordRequest $request, Keyword $keyword)
    {
        $validated = $request->validated();

        $keyword->update([
            'word' => trim($validated['word']),
        ]);

        $keyword->refresh();

        $this->syncPortugueseTranslation($keyword);

        TranslationDispatcher::keyword($keyword);

        return KeywordResource::make(
            $keyword->load('translations')
        );
    }

    public function destroy(Keyword $keyword)
    {
        $keyword->delete();

        return response()->json(null, 204);
    }

    private function syncPortugueseTranslation(Keyword $keyword): void
    {
        KeywordTranslation::updateOrCreate(
            [
                'keyword_id' => $keyword->id,
                'locale' => KeywordTranslation::LOCALE_PT_BR,
            ],
            [
                'word' => $keyword->word,
                'translation_status' => KeywordTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
    }
}
