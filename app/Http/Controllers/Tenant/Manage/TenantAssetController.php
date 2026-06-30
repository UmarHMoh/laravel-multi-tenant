<?php

namespace App\Http\Controllers\Tenant\Manage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class TenantAssetController extends Controller
{
    /**
     * Serve a tenant-specific public asset safely.
     */
    public function __invoke($path)
    {
        $path = $this->safeTenantAssetPath((string) $path);

        abort_unless($path !== null, 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';

        abort_unless(str_starts_with((string) $mimeType, 'image/'), 404);

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function safeTenantAssetPath(string $path): ?string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            return null;
        }

        if (! preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $path)) {
            return null;
        }

        $allowedPrefixes = [
            'products/',
            'tenant-website/',
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $path;
            }
        }

        return null;
    }
}
