<?php

namespace Selpol\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Selpol\Http\Response;
use Selpol\Service\HttpService;

class InternalMiddleware implements MiddlewareInterface
{
    private array $trust;
    private ?LoggerInterface $logger;

    public function __construct()
    {
        $this->trust = config('internal.trust') ?? ['127.0.0.1/32'];
        $this->logger = (config('internal.logger') ?? false) ? logger('internal') : null;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $ip = $request->getHeader('X-Real-Ip');

        $this->logger?->debug('Request ' . $request->getRequestTarget(), ['ip' => $ip]);

        if (count($ip) === 0) {
            /** @var HttpService $http */
            $http = $request->getAttribute('http');

            return $http->createResponse(404)
                ->withJson(['code' => 404, 'name' => Response::$codes[404]['name'], 'message' => Response::$codes[404]['message']]);
        }

        foreach ($this->trust as $item)
            if ($this->ipInRange($ip[0], $item)) {
                $response = $handler->handle($request);

                $this->logger?->debug('Response ' . $request->getRequestTarget(), ['ip' => $ip, 'code' => $response->getStatusCode()]);

                return $response;
            }

        /** @var HttpService $http */
        $http = $request->getAttribute('http');

        return $http->createResponse(404)
            ->withJson(['code' => 404, 'name' => Response::$codes[404]['name'], 'message' => Response::$codes[404]['message']]);
    }

    private function ipInRange(string $ip, string $range): bool
    {
        if (!strpos($range, '/'))
            $range .= '/32';

        list($range, $netmask) = explode('/', $range, 2);

        $ip_decimal = ip2long($ip);
        $range_decimal = ip2long($range);
        $netmask_decimal = ~(pow(2, (32 - $netmask)) - 1);

        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }
}