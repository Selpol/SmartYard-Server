<?php declare(strict_types=1);

namespace Selpol\Middleware\Mobile;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Selpol\Feature\House\HouseFeature;
use Selpol\Framework\Router\Route\RouteMiddleware;
use Selpol\Service\Auth\User\SubscriberAuthUser;
use Selpol\Service\AuthService;

readonly class SubscriberMiddleware extends RouteMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $auth = container(AuthService::class);

        $token = $auth->getTokenOrThrow();

        $subscribers = container(HouseFeature::class)->getSubscribers('aud_jti', $token->getAudJti());

        if (!$subscribers || count($subscribers) === 0)
            return json_response(401, body: ['code' => 401, 'message' => 'Абонент не найден']);

        $auth->setUser(new SubscriberAuthUser($subscribers[0]));

        return $handler->handle($request);
    }
}