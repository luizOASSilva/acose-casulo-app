<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaFileRequest;
use App\Http\Resources\MediaFileResource;
use App\Models\Activity;
use App\Models\Article;
use App\Models\MediaFile;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaLibraryController extends Controller
{
    private string $disk = 'public';

    private array $allowedCollections = [
        'articles',
        'activities',
        'partners',
        'general',
    ];

    public function index(Request $request, string $collection)
    {
        $this->authorizeAdmin($request);
        $this->validateCollection($collection);

        $files = MediaFile::query()
            ->where('collection', $collection)
            ->latest()
            ->get();

        return MediaFileResource::collection($files);
    }

    public function store(StoreMediaFileRequest $request, string $collection): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->validateCollection($collection);

        $file = $request->file('file');

        abort_unless(
            $file && $file->isValid(),
            422,
            'Arquivo inválido ou não enviado.'
        );

        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === '') {
            $extension = $file->guessExtension() ?: 'bin';
        }

        $filename = $collection . '-' . Str::uuid() . '.' . $extension;
        $directory = 'media/' . $collection;

        $disk = Storage::disk($this->disk);

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $path = $file->storeAs(
            $directory,
            $filename,
            $this->disk
        );

        if (! is_string($path) || trim($path) === '' || $path === '0') {
            throw new RuntimeException(
                'Falha ao salvar arquivo no storage público. Verifique permissões de storage/app/public e o link public/storage.'
            );
        }

        if (! $disk->exists($path)) {
            throw new RuntimeException(
                'O arquivo foi processado, mas não foi encontrado no storage após o upload.'
            );
        }

        $relativeUrl = Storage::url($path);

        $mediaFile = MediaFile::query()->create([
            'collection' => $collection,
            'disk' => $this->disk,
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'url' => asset($relativeUrl),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size' => $file->getSize() ?: $disk->size($path) ?: 0,
            'created_by' => $request->user('admin')?->id,
        ]);

        return MediaFileResource::make($mediaFile)
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(
        Request $request,
        string $collection,
        MediaFile $mediaFile
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $this->validateCollection($collection);

        abort_unless(
            $mediaFile->collection === $collection,
            404,
            'Arquivo não encontrado nesta coleção.'
        );

        abort_if(
            $this->isMediaInUse($mediaFile),
            422,
            'Essa imagem está em uso. Remova ou troque a imagem do conteúdo antes de apagar.'
        );

        if (
            $mediaFile->path &&
            $mediaFile->path !== '0' &&
            Storage::disk($mediaFile->disk)->exists($mediaFile->path)
        ) {
            Storage::disk($mediaFile->disk)->delete($mediaFile->path);
        }

        $mediaFile->delete();

        return response()->json([
            'message' => 'Arquivo removido com sucesso.',
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            $request->user('admin'),
            403,
            'Apenas administradores podem gerenciar mídias.'
        );
    }

    private function validateCollection(string $collection): void
    {
        abort_unless(
            in_array($collection, $this->allowedCollections, true),
            422,
            'Coleção de mídia inválida.'
        );
    }

    private function isMediaInUse(MediaFile $mediaFile): bool
    {
        if (! $mediaFile->path || $mediaFile->path === '0') {
            return false;
        }

        $absoluteUrl = $mediaFile->url;
        $relativeUrl = Storage::url($mediaFile->path);

        return Article::query()
            ->whereHas('publication.media', function ($query) use ($absoluteUrl, $relativeUrl) {
                $query
                    ->where('url', $absoluteUrl)
                    ->orWhere('url', $relativeUrl);
            })
            ->exists()
            || Activity::query()
                ->whereHas('publication.media', function ($query) use ($absoluteUrl, $relativeUrl) {
                    $query
                        ->where('url', $absoluteUrl)
                        ->orWhere('url', $relativeUrl);
                })
                ->exists()
            || Partner::query()
                ->where(function ($query) use ($absoluteUrl, $relativeUrl, $mediaFile) {
                    $query
                        ->where('logo_path', $mediaFile->path)
                        ->orWhere('logo_path', $absoluteUrl)
                        ->orWhere('logo_path', $relativeUrl);
                })
                ->exists();
    }
}
