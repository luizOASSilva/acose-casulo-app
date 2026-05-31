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
        $keywords = Keyword::query()
            ->when($request->filled('busca'), function ($query) use ($request) {
                $query->where('word', 'like', '%' . $request->busca . '%');
            })
            ->orderBy('word')
            ->paginate($request->integer('per_page', 10));

        return KeywordResource::collection($keywords);
    }

    public function store(StoreKeywordRequest $request)
    {
        $keyword = Keyword::create($request->validated());

        return KeywordResource::make($keyword)->response()->setStatusCode(201);
    }

    public function show(Keyword $keyword)
    {
        return KeywordResource::make($keyword);
    }

    public function update(UpdateKeywordRequest $request, Keyword $keyword)
    {
        $keyword->update($request->validated());

        return KeywordResource::make($keyword);
    }

    public function destroy(Keyword $keyword)
    {
        $keyword->delete();

        return response()->json(null, 204);
    }
}
