<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Sends pages gzip-compressed (when the browser accepts it) and always with their exact length.
 *
 * The library pages repeat many verses, so their HTML compresses to about a tenth. Small, complete
 * responses also avoid a bug in PHP's built-in server on Windows, which can cut large pages short
 * and keep the browser "loading" for ~20 seconds. A web server in front may compress too; it skips
 * responses that already carry a Content-Encoding.
 */
class CompressPages
{
    /**
     * Below this size compression is not worth it.
     */
    private const MIN_BYTES = 1024;

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse
            || $response->headers->has('Content-Encoding') || $response->headers->has('Transfer-Encoding')) {
            return $response;
        }

        $content = (string) $response->getContent();
        $isText = str_starts_with((string) $response->headers->get('Content-Type'), 'text/')
            || str_contains((string) $response->headers->get('Content-Type'), 'json');

        if ($isText && strlen($content) >= self::MIN_BYTES && str_contains((string) $request->header('Accept-Encoding'), 'gzip')) {
            $content = gzencode($content, 6);
            $response->setContent($content);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->setVary('Accept-Encoding', false);
        }

        $response->headers->set('Content-Length', (string) strlen($content));

        return $response;
    }
}
