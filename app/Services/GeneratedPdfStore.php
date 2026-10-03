<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GeneratedPdfStore
{
    /**
     * Return the public URL for this document's latest PDF.
     *
     * When the render inputs match the stored fingerprint and the file is
     * still on disk, the existing file is reused. Otherwise the new file is
     * written, the document's stored path is updated, and the previous PDF
     * is removed from the public disk.
     *
     * @param  callable(string $absolutePath): void  $write
     * @param  array<string, mixed>  $renderContext
     * @param  array<string, mixed>  $payload
     */
    public function put(
        Model $document,
        string $directory,
        string $prefix,
        array $renderContext,
        array $payload,
        callable $write,
    ): string {
        $disk = Storage::disk('public');
        $fingerprint = $this->fingerprint($renderContext, $payload);
        $currentPath = $document->getAttribute('pdf_path');
        $currentFingerprint = $document->getAttribute('pdf_fingerprint');

        if (
            $document->exists
            && is_string($currentPath)
            && $currentPath !== ''
            && $currentFingerprint === $fingerprint
            && $disk->exists($currentPath)
        ) {
            return $disk->url($currentPath);
        }

        $relativePath = $this->relativePath($document, $directory, $prefix, $fingerprint);
        $absolutePath = $disk->path($relativePath);
        $parent = dirname($absolutePath);

        if (! is_dir($parent)) {
            mkdir($parent, 0775, true);
        }

        $write($absolutePath);

        if (! is_file($absolutePath)) {
            throw new \RuntimeException(__('messages.pdf_generation_failed'));
        }

        if ($document->exists) {
            $document->pdf_path = $relativePath;
            $document->pdf_fingerprint = $fingerprint;
            $document->timestamps = false;
            $document->save();
            $document->timestamps = true;
        }

        if (
            $document->exists
            && is_string($currentPath)
            && $currentPath !== ''
            && $currentPath !== $relativePath
        ) {
            $this->deleteOwnedPdf($directory, $currentPath);
        }

        return $disk->url($relativePath);
    }

    /**
     * @param  array<string, mixed>  $renderContext
     * @param  array<string, mixed>  $payload
     */
    public function fingerprint(array $renderContext, array $payload): string
    {
        unset($payload['created_at'], $payload['updated_at'], $payload['pdf_path'], $payload['pdf_fingerprint']);

        $encoded = json_encode(
            $this->sortRecursive([
                'render' => $renderContext,
                'payload' => $payload,
            ]),
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        return hash('sha256', $encoded);
    }

    /**
     * @return array<string, mixed>
     */
    public function renderContext(Model $template, string $viewPath): array
    {
        $updatedAt = $template->getAttribute('updated_at');

        return [
            'template_id' => $template->getKey(),
            'template_name' => (string) $template->getAttribute('name'),
            'template_updated_at' => $updatedAt instanceof \DateTimeInterface
                ? $updatedAt->format(\DateTimeInterface::ATOM)
                : null,
            'view_mtime' => $this->viewMtime($viewPath),
        ];
    }

    private function relativePath(Model $document, string $directory, string $prefix, string $fingerprint): string
    {
        $suffix = substr($fingerprint, 0, 12);

        if ($document->exists && $document->getKey()) {
            return $directory.'/'.$document->getKey().'-'.$suffix.'.pdf';
        }

        return $directory.'/'.uniqid($prefix.'_', true).'.pdf';
    }

    private function deleteOwnedPdf(string $directory, string $path): void
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (! str_starts_with($normalized, $directory.'/') || str_contains($normalized, '..')) {
            return;
        }

        if (! str_ends_with(strtolower($normalized), '.pdf')) {
            return;
        }

        Storage::disk('public')->delete($normalized);
    }

    private function viewMtime(string $viewPath): ?int
    {
        $viewFile = resource_path('views/'.str_replace('.', '/', $viewPath).'.blade.php');

        return is_file($viewFile) ? filemtime($viewFile) : null;
    }

    private function sortRecursive(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->sortRecursive($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->sortRecursive($item);
        }

        return $value;
    }
}
