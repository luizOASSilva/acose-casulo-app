<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $partners = Partner::query()
            ->with('admin')
            ->when(! $request->user('admin'), function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return PartnerResource::collection($partners);
    }

    public function store(StorePartnerRequest $request): PartnerResource
    {
        $validated = $request->validated();

        $partner = Partner::query()->create([
            'admin_id' => $request->user('admin')?->id,
            'name' => $validated['name'],
            'logo_path' => $this->normalizeLogoPath($validated['logo_url']),
            'website_url' => $validated['website_url'] ?? null,
            'bg_color' => $validated['bg_color'] ?? '#ffffff',
            'order' => $validated['order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return new PartnerResource(
            $partner->fresh('admin')
        );
    }

    public function update(
        UpdatePartnerRequest $request,
        Partner $partner
    ): PartnerResource {
        $validated = $request->validated();

        $data = [];

        if (array_key_exists('name', $validated)) {
            $data['name'] = $validated['name'];
        }

        if (array_key_exists('logo_url', $validated)) {
            $data['logo_path'] = $this->normalizeLogoPath($validated['logo_url']);
        }

        if (array_key_exists('website_url', $validated)) {
            $data['website_url'] = $validated['website_url'];
        }

        if (array_key_exists('bg_color', $validated)) {
            $data['bg_color'] = $validated['bg_color'] ?? '#ffffff';
        }

        if (array_key_exists('order', $validated)) {
            $data['order'] = $validated['order'] ?? 0;
        }

        if (array_key_exists('is_active', $validated)) {
            $data['is_active'] = $validated['is_active'];
        }

        $partner->update($data);

        return new PartnerResource(
            $partner->fresh('admin')
        );
    }

    public function destroy(Partner $partner): JsonResponse
    {
        $partner->delete();

        return response()->json([
            'message' => 'Parceiro removido com sucesso.',
        ]);
    }

    private function normalizeLogoPath(string $logoUrl): string
    {
        if (Str::startsWith($logoUrl, asset('storage/'))) {
            return Str::after($logoUrl, asset('storage/'));
        }

        if (Str::startsWith($logoUrl, '/storage/')) {
            return Str::after($logoUrl, '/storage/');
        }

        return $logoUrl;
    }
}
