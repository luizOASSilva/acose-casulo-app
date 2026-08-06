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
        $documents = Document::query()
            ->with([
                'category',
                'admin',
            ])
            ->when(
                request('year'),
                fn ($query, $year) => $query->where('year', $year)
            )
            ->when(
                request('category_id'),
                fn ($query, $id) => $query->where('category_id', $id)
            )
            ->latest()
            ->paginate();

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request)
    {
        $validated = $request->validated();

        $adminId = $request->user('admin')?->id
            ?? $request->user()?->id;

        abort_unless(
            $adminId,
            403,
            'Administrador não autenticado.'
        );

        $document = Document::query()->create([
            ...$validated,
            'admin_id' => $adminId,
        ]);

        return DocumentResource::make(
            $document->fresh()->load([
                'category',
                'admin',
            ])
        )
            ->response()
            ->setStatusCode(201);
    }

    public function show(Document $document)
    {
        return DocumentResource::make(
            $document->load([
                'category',
                'admin',
            ])
        );
    }

    public function update(
        UpdateDocumentRequest $request,
        Document $document
    ) {
        $validated = $request->validated();

        unset($validated['admin_id']);

        $document->update($validated);

        $document->refresh();

        return DocumentResource::make(
            $document->load([
                'category',
                'admin',
            ])
        );
    }

    public function destroy(Document $document)
    {
        $document->delete();

        return response()->json(null, 204);
    }
}
