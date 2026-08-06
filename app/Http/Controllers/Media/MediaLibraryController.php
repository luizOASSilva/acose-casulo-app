<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaFileRequest;
use App\Http\Resources\MediaFileResource;
use App\Models\Activity;
use App\Models\Article;
use App\Models\MediaFile;
use App\Models\Partner;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

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

    public function store(
        StoreMediaFileRequest $request,
        string $collection
    ): JsonResponse {
        $this->authorizeAdmin($request);
        $this->validateCollection($collection);

        $file = $request->file('file');

        abort_unless(
            $file instanceof UploadedFile && $file->isValid(),
            422,
            'Arquivo inválido ou não enviado.'
        );

        $disk = Storage::disk($this->disk);
        $directory = 'media/'.$collection;

        $extension = $this->resolveExtension($file);

        $filename = sprintf(
            '%s-%s.%s',
            $collection,
            Str::uuid()->toString(),
            $extension
        );

        try {
            $this->ensureDirectoryExists(
                disk: $disk,
                directory: $directory
            );

            /*
             * Usa diretamente o disco configurado.
             * Isso evita divergências entre storeAs() e o disco utilizado.
             */
            $path = $disk->putFileAs(
                $directory,
                $file,
                $filename,
                [
                    'visibility' => 'public',
                ]
            );

            if (
                ! is_string($path) ||
                trim($path) === '' ||
                $path === '0'
            ) {
                throw new RuntimeException(
                    'O filesystem não retornou o caminho do arquivo salvo.'
                );
            }

            if (! $disk->exists($path)) {
                throw new RuntimeException(
                    'O upload terminou, mas o arquivo não foi encontrado no storage.'
                );
            }

            $url = $this->generatePublicUrl(
                disk: $disk,
                path: $path
            );

            $mediaFile = MediaFile::query()->create([
                'collection' => $collection,
                'disk' => $this->disk,
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'mime_type' => $file->getMimeType()
                    ?: $file->getClientMimeType(),
                'size' => $file->getSize()
                    ?: $disk->size($path)
                    ?: 0,
                'created_by' => $request->user('admin')?->id,
            ]);

            return MediaFileResource::make($mediaFile)
                ->response()
                ->setStatusCode(201);
        } catch (Throwable $exception) {
            Log::error('Falha ao enviar arquivo para a biblioteca de mídia.', [
                'disk' => $this->disk,
                'collection' => $collection,
                'directory' => $directory,
                'filename' => $filename,
                'storage_root' => config(
                    "filesystems.disks.{$this->disk}.root"
                ),
                'app_url' => config('app.url'),
                'file_valid' => $file->isValid(),
                'file_error' => $file->getError(),
                'file_error_message' => $file->getErrorMessage(),
                'temporary_path' => $file->getPathname(),
                'temporary_file_exists' => is_file($file->getPathname()),
                'temporary_file_readable' => is_readable(
                    $file->getPathname()
                ),
                'exception' => $exception,
            ]);

            throw new RuntimeException(
                'Não foi possível salvar a imagem. Consulte os logs do servidor.',
                previous: $exception
            );
        }
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
            $mediaFile->path !== '0'
        ) {
            $disk = Storage::disk(
                $mediaFile->disk ?: $this->disk
            );

            if ($disk->exists($mediaFile->path)) {
                $disk->delete($mediaFile->path);
            }
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
            in_array(
                $collection,
                $this->allowedCollections,
                true
            ),
            422,
            'Coleção de mídia inválida.'
        );
    }

    private function resolveExtension(
        UploadedFile $file
    ): string {
        $extension = strtolower(
            trim($file->getClientOriginalExtension())
        );

        if ($extension !== '') {
            return $extension;
        }

        return strtolower(
            $file->guessExtension() ?: 'bin'
        );
    }

    private function ensureDirectoryExists(
        Filesystem $disk,
        string $directory
    ): void {
        if ($disk->directoryExists($directory)) {
            return;
        }

        $created = $disk->makeDirectory($directory);

        if (! $created || ! $disk->directoryExists($directory)) {
            throw new RuntimeException(
                "Não foi possível criar o diretório {$directory}."
            );
        }
    }

    private function generatePublicUrl(
        Filesystem $disk,
        string $path
    ): string {
        $url = $disk->url($path);

        /*
         * Caso algum driver retorne apenas /storage/..., monta a URL
         * absoluta usando APP_URL do backend.
         */
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $appUrl = rtrim(
            (string) config('app.url'),
            '/'
        );

        return $appUrl.'/'.ltrim($url, '/');
    }

    private function isMediaInUse(MediaFile $mediaFile): bool
    {
        if (
            ! $mediaFile->path ||
            $mediaFile->path === '0'
        ) {
            return false;
        }

        $disk = Storage::disk(
            $mediaFile->disk ?: $this->disk
        );

        $absoluteUrl = $mediaFile->url;
        $generatedUrl = $disk->url($mediaFile->path);
        $relativeUrl = '/storage/'.ltrim(
            Str::after($mediaFile->path, 'storage/'),
            '/'
        );

        return Article::query()
            ->whereHas(
                'publication.media',
                function ($query) use (
                    $absoluteUrl,
                    $generatedUrl,
                    $relativeUrl
                ) {
                    $query->where(function ($mediaQuery) use (
                        $absoluteUrl,
                        $generatedUrl,
                        $relativeUrl
                    ) {
                        $mediaQuery
                            ->where('url', $absoluteUrl)
                            ->orWhere('url', $generatedUrl)
                            ->orWhere('url', $relativeUrl);
                    });
                }
            )
            ->exists()
            || Activity::query()
                ->whereHas(
                    'publication.media',
                    function ($query) use (
                        $absoluteUrl,
                        $generatedUrl,
                        $relativeUrl
                    ) {
                        $query->where(function ($mediaQuery) use (
                            $absoluteUrl,
                            $generatedUrl,
                            $relativeUrl
                        ) {
                            $mediaQuery
                                ->where('url', $absoluteUrl)
                                ->orWhere('url', $generatedUrl)
                                ->orWhere('url', $relativeUrl);
                        });
                    }
                )
                ->exists()
            || Partner::query()
                ->where(function ($query) use (
                    $absoluteUrl,
                    $generatedUrl,
                    $relativeUrl,
                    $mediaFile
                ) {
                    $query
                        ->where('logo_path', $mediaFile->path)
                        ->orWhere('logo_path', $absoluteUrl)
                        ->orWhere('logo_path', $generatedUrl)
                        ->orWhere('logo_path', $relativeUrl);
                })
                ->exists();
    }
}
