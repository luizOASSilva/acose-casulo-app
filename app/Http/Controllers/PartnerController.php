<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Http\Resources\PartnerResource;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartnerController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $partners = Partner::orderBy('order')->get();
        return PartnerResource::collection($partners);
    }

    public function store(StorePartnerRequest $request): PartnerResource
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners', 'public');
        }

        $partner = $request->user()->partners()->create($data);

        return new PartnerResource($partner);
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): PartnerResource
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('partners', 'public');
        }

        $partner->update($data);

        return new PartnerResource($partner->fresh());
    }

    public function destroy(Partner $partner)
    {
        if ($partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
        }

        $partner->delete();

        return response()->noContent();
    }
}
