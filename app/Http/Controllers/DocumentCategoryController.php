<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentCategory\StoreDocumentCategoryRequest;
use App\Http\Requests\DocumentCategory\UpdateDocumentCategoryRequest;
use App\Http\Resources\DocumentCategoryResource;
use App\Models\DocumentCategory;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::withCount('documents')
            ->orderBy('order')
            ->get();

        return DocumentCategoryResource::collection($categories);
    }

    public function store(StoreDocumentCategoryRequest $request)
    {
        $category = DocumentCategory::create($request->validated());

        return DocumentCategoryResource::make($category)->response()->setStatusCode(201);
    }

    public function show(DocumentCategory $documentCategory)
    {
        return DocumentCategoryResource::make($documentCategory->load('documents'));
    }

    public function update(UpdateDocumentCategoryRequest $request, DocumentCategory $documentCategory)
    {
        $documentCategory->update($request->validated());

        return DocumentCategoryResource::make($documentCategory);
    }

    public function destroy(DocumentCategory $documentCategory)
    {
        $documentCategory->delete();

        return response()->json(null, 204);
    }
}
