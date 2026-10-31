<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * خط ثمانية from the site's own address, cached for a year.
 *
 * The files are not in the (public) repository: on the hosted site they live in a public bucket
 * (THMANYAH_FONTS_URL), locally in resources/fonts/thmanyah. The bucket sends no cache headers, so
 * the browser re-checked every font on every page and drew it first in a fallback font. Served from
 * here they are fetched once per server, then kept by the browser: every page opens in ثمانية.
 */
class FontController extends Controller
{
    /**
     * The only files served: the seven faces the site uses (components/thmanyah-faces).
     */
    public const FILES = [
        'thmanyahsans-Regular', 'thmanyahsans-Medium', 'thmanyahsans-Bold',
        'thmanyahserifdisplay-Bold', 'thmanyahserifdisplay-Black',
        'thmanyahseriftext-Regular', 'thmanyahseriftext-Medium',
    ];

    public function thmanyah(string $file): BinaryFileResponse
    {
        abort_unless(in_array($file, self::FILES, true), 404);

        $path = $this->localCopy($file.'.woff2');
        abort_if($path === null, 404);

        return response()->file($path, [
            'Content-Type' => 'font/woff2',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * The font on this server's disk: the project's own copy, or one fetched once from the bucket.
     */
    private function localCopy(string $name): ?string
    {
        $bundled = resource_path('fonts/thmanyah/'.$name);
        if (File::exists($bundled)) {
            return $bundled;
        }

        $cached = storage_path('app/fonts/thmanyah/'.$name);
        if (File::exists($cached)) {
            return $cached;
        }

        $bucket = rtrim((string) config('services.thmanyah.url'), '/');
        if ($bucket === '') {
            return null;
        }

        $response = Http::timeout(10)->get($bucket.'/'.$name);
        if (! $response->successful() || $response->body() === '') {
            return null;
        }

        File::ensureDirectoryExists(dirname($cached));
        File::put($cached, $response->body());

        return $cached;
    }
}
