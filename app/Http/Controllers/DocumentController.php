<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\DocumentTranslation;
use App\Support\TranslationDispatcher;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::query()
            ->with([
                'translations',
                'category.translations',
                'admin',
            ])
            ->when(request('year'), fn ($query, $year) => $query->where('year', $year))
            ->when(request('category_id'), fn ($query, $id) => $query->where('category_id', $id))
            ->latest()
            ->paginate();

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request)
    {
        $validated = $request->validated();

        $adminId = $request->user('admin')?->id ?? $request->user()?->id;

        abort_unless($adminId, 403, 'Administrador não autenticado.');

        $document = Document::query()->create([
            ...$validated,
            'admin_id' => $adminId,
        ]);

        $this->syncPortugueseTranslation($document);

        TranslationDispatcher::document($document);

        return DocumentResource::make(
            $document->fresh()->load([
                'translations',
                'category.translations',
                'admin',
            ])
        )->response()->setStatusCode(201);
    }

    public function show(Document $document)
    {
        return DocumentResource::make(
            $document->load([
                'translations',
                'category.translations',
                'admin',
            ])
        );
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $validated = $request->validated();

        unset($validated['admin_id']);

        $document->update($validated);

        $document->refresh();

        $this->syncPortugueseTranslation($document);

        TranslationDispatcher::document($document);

        return DocumentResource::make(
            $document->load([
                'translations',
                'category.translations',
                'admin',
            ])
        );
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(null, 204);
    }

    private function syncPortugueseTranslation(Document $document): void
    {
        DocumentTranslation::updateOrCreate(
            [
                'document_id' => $document->id,
                'locale' => DocumentTranslation::LOCALE_PT_BR,
            ],
            [
                'title' => $document->title,
                'translation_status' => DocumentTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
    }
}
