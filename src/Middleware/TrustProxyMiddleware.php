<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Sets $request->trustProxy = true so CakePHP honours X-Forwarded-* headers.
 * Restrict via env APP_TRUSTED_PROXIES (comma-separated IPs) or leave empty/* to trust all.
 */
class TrustProxyMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request instanceof ServerRequest) {
            // Only trust X-Forwarded-* when the deployment explicitly opts in via
            // APP_TRUSTED_PROXIES. An empty value now means "trust no proxy" — the
            // safe default; previously empty trusted EVERY client's forwarded
            // headers, letting anyone spoof their IP/proto.
            $trustedList = trim((string)env('APP_TRUSTED_PROXIES', ''));
            if ($trustedList === '*') {
                $request->trustProxy = true;
            } elseif ($trustedList !== '') {
                $remote = $request->getEnv('REMOTE_ADDR');
                $allowed = array_map('trim', explode(',', $trustedList));
                if ($remote !== null && in_array($remote, $allowed, true)) {
                    $request->trustProxy = true;
                }
            }
        }
        return $handler->handle($request);
    }
}
