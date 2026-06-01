<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminActionLogResource;
use App\Models\Admin;
use App\Models\AdminActionLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminActionLogController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->ensureAuthenticatedAdmin();

        $query = AdminActionLog::query()
            ->with('admin')
            ->latest();

        if ($request->filled('busca')) {
            $search = $request->string('busca')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('admin_name', 'like', "%{$search}%")
                    ->orWhere('subject_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where(
                'action',
                'like',
                $request->string('action')->toString() . '%'
            );
        }

        if ($request->filled('operation')) {
            $operation = $request->string('operation')->toString();

            if (in_array($operation, ['created', 'updated', 'deleted'], true)) {
                $query->where('action', 'like', "%.{$operation}");
            }
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->integer('admin_id'));
        }

        return AdminActionLogResource::collection(
            $query->paginate(
                perPage: min((int) $request->integer('per_page', 20), 50)
            )
        );
    }

    public function show(AdminActionLog $actionLog): AdminActionLogResource
    {
        $this->ensureMaster();

        $actionLog->loadMissing('admin');

        return AdminActionLogResource::make($actionLog);
    }

    public function filters(): JsonResponse
    {
        $this->ensureAuthenticatedAdmin();

        $admins = AdminActionLog::query()
            ->select('admin_id', 'admin_name')
            ->whereNotNull('admin_id')
            ->whereNotNull('admin_name')
            ->groupBy('admin_id', 'admin_name')
            ->orderBy('admin_name')
            ->get()
            ->map(fn (AdminActionLog $log) => [
                'id' => $log->admin_id,
                'name' => $log->admin_name,
            ])
            ->values();

        return response()->json([
            'admins' => $admins,
            'types' => [
                [
                    'value' => 'article',
                    'label' => 'Artigos',
                ],
                [
                    'value' => 'activity',
                    'label' => 'Atividades',
                ],
                [
                    'value' => 'partner',
                    'label' => 'Parceiros',
                ],
                [
                    'value' => 'document',
                    'label' => 'Documentos',
                ],
                [
                    'value' => 'media',
                    'label' => 'Mídias',
                ],
                [
                    'value' => 'setting',
                    'label' => 'Configurações',
                ],
                [
                    'value' => 'keyword',
                    'label' => 'Palavras-chave',
                ],
            ],
            'operations' => [
                [
                    'value' => 'created',
                    'label' => 'Criado/enviado',
                ],
                [
                    'value' => 'updated',
                    'label' => 'Editado',
                ],
                [
                    'value' => 'deleted',
                    'label' => 'Removido',
                ],
            ],
        ]);
    }

    private function ensureAuthenticatedAdmin(): void
    {
        abort_unless(
            auth('admin')->check(),
            403
        );
    }

    private function ensureMaster(): void
    {
        abort_unless(
            auth('admin')->check()
                && auth('admin')->user()?->role === Admin::ROLE_MASTER,
            403
        );
    }
}
