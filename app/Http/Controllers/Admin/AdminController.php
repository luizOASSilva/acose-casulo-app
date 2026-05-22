<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    public function index()
    {
        return AdminResource::collection(
            Admin::query()
                ->latest()
                ->paginate(20)
        );
    }

    public function store(StoreAdminRequest $request)
    {
        $admin = Admin::create($request->validated());

        return AdminResource::make($admin)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Admin $admin)
    {
        return AdminResource::make($admin);
    }

    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $admin->update($data);

        return AdminResource::make($admin);
    }

    public function destroy(Admin $admin): JsonResponse
    {
        if (auth('admin')->id() === $admin->id) {
            return response()->json([
                'message' => 'Você não pode remover o próprio usuário logado.',
            ], 422);
        }

        $admin->delete();

        return response()->json(null, 204);
    }
}

