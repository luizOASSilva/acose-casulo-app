<?php

namespace App\Http\Controllers;

use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $query = Partner::query()
            ->with('admin')
            ->orderBy('order')
            ->orderBy('name');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));

            $query->where(function ($partnerQuery) use ($search) {
                $partnerQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('logo_alt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                default => null,
            };
        }

        if ($request->boolean('all')) {
            return PartnerResource::collection(
                $query->get()
            );
        }

        $perPage = (int) $request->input('per_page', 12);
        $perPage = max(1, min($perPage, 48));

        return PartnerResource::collection(
            $query
                ->paginate($perPage)
                ->withQueryString()
        );
    }

    public function publicIndex()
    {
        return PartnerResource::collection(
            Partner::query()
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(StorePartnerRequest $request)
    {
        $validated = $request->validated();

        $adminId = $request->user('admin')?->id
            ?? $request->user()?->id;

        abort_unless(
            $adminId,
            403,
            'Administrador não autenticado.'
        );

        $partner = Partner::create([
            ...$validated,
            'admin_id' => $adminId,
        ]);

        return PartnerResource::make(
            $partner->fresh('admin')
        )
            ->response()
            ->setStatusCode(201);
    }

    public function show(Partner $partner)
    {
        return PartnerResource::make(
            $partner->load('admin')
        );
    }

    public function update(
        UpdatePartnerRequest $request,
        Partner $partner
    ) {
        $validated = $request->validated();

        unset($validated['admin_id']);

        $partner->update($validated);

        $partner->refresh();

        return PartnerResource::make(
            $partner->load('admin')
        );
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();

        return response()->json(null, 204);
    }
}
