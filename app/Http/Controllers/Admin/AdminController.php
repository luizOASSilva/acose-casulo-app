<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $data = $request->validated();

        if (empty($data['password'])) {
            $data['password'] = Hash::make(Str::random(32));
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        unset($data['password_confirmation']);

        $admin = Admin::create($data);

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

        if (array_key_exists('password', $data)) {
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        unset($data['password_confirmation']);

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
