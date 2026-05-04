<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('category')
            ->when(request('year'), fn ($q, $year) => $q->where('year', $year))
            ->when(request('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->paginate();

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request)
    {
        $document = Document::create($request->validated());

        return DocumentResource::make($document->load('category'))->response()->setStatusCode(201);
    }

    public function show(Document $document)
    {
        return DocumentResource::make($document->load('category'));
    }

    public function update(UpdateDocumentRequest $request, Document $document)
    {
        $document->update($request->validated());

        return DocumentResource::make($document->load('category'));
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(null, 204);
    }
}
