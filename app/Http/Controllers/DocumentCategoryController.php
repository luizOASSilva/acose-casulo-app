<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentCategory\StoreDocumentCategoryRequest;
use App\Http\Requests\DocumentCategory\UpdateDocumentCategoryRequest;
use App\Http\Resources\DocumentCategoryResource;
use App\Models\DocumentCategory;
use App\Models\DocumentCategoryTranslation;
use App\Support\TranslationDispatcher;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::query()
            ->with('translations')
            ->withCount('documents')
            ->orderBy('order')
            ->get();

        return DocumentCategoryResource::collection($categories);
    }

    public function store(StoreDocumentCategoryRequest $request)
    {
        $category = DocumentCategory::create($request->validated());

        $this->syncPortugueseTranslation($category);

        TranslationDispatcher::documentCategory($category);

        return DocumentCategoryResource::make(
            $category->fresh(['translations'])
        )->response()->setStatusCode(201);
    }

    public function show(DocumentCategory $documentCategory)
    {
        return DocumentCategoryResource::make(
            $documentCategory->load([
                'translations',
                'documents.translations',
                'documents.category.translations',
            ])
        );
    }

    public function update(
        UpdateDocumentCategoryRequest $request,
        DocumentCategory $documentCategory
    ) {
        $documentCategory->update($request->validated());

        $documentCategory->refresh();

        $this->syncPortugueseTranslation($documentCategory);

        TranslationDispatcher::documentCategory($documentCategory);

        return DocumentCategoryResource::make(
            $documentCategory->load('translations')
        );
    }

    public function destroy(DocumentCategory $documentCategory)
    {
        $documentCategory->delete();

        return response()->json(null, 204);
    }

    private function syncPortugueseTranslation(DocumentCategory $category): void
    {
        DocumentCategoryTranslation::updateOrCreate(
            [
                'document_category_id' => $category->id,
                'locale' => DocumentCategoryTranslation::LOCALE_PT_BR,
            ],
            [
                'name' => $category->name,
                'description' => $category->description,
                'translation_status' => DocumentCategoryTranslation::STATUS_ORIGINAL,
                'translated_at' => null,
            ]
        );
    }
}
