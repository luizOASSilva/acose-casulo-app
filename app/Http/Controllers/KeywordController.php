<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeywordRequest;
use App\Http\Requests\UpdateKeywordRequest;
use App\Http\Resources\KeywordResource;
use App\Models\Keyword;

class KeywordController extends Controller
{
    public function index()
    {
        return KeywordResource::collection(Keyword::paginate());
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
