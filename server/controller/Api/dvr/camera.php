<?php declare(strict_types=1);

namespace Selpol\Controller\Api\dvr;

use Psr\Http\Message\ResponseInterface;
use Selpol\Controller\Api\Api;
use Selpol\Entity\Model\Dvr\DvrServer;
use Selpol\Service\DeviceService;

readonly class camera extends Api
{
    public static function POST(array $params): ResponseInterface
    {
        $validate = validator($params, [
            '_id' => rule()->id(),

            'query' => rule()->required()->string()->nonNullable()
        ]);

        $dvr = DvrServer::findById($validate['_id'], setting: setting()->nonNullable());

        if ($dvr->type === 'flussonic') {
            $auth = validator($params, [
                'username' => rule()->required()->string()->nonNullable(),
                'password' => rule()->required()->string()->nonNullable(),
            ]);

            if ($camera = container(DeviceService::class)->dvr($dvr->type, $dvr->url, $auth['username'], $auth['password'])?->getCameraId($validate['query']))
                return self::success($camera);
        } else if ($dvr->type === 'trassir') {
            $auth = explode('&', $dvr->token);

            $auth = array_reduce($auth, static function (array $previous, string $current) {
                $value = explode('=', $current);

                $previous[$value[0]] = $value[1];

                return $previous;
            }, []);

            if ($camera = container(DeviceService::class)->dvr($dvr->type, $dvr->url, $auth['username'], $auth['password'])?->getCameraId($validate['query']))
                return self::success($camera);
        }

        return self::error('Камера не найдена', 404);
    }

    public static function index(): array|bool
    {
        return ['POST' => '[Dvr] Найти идентификатор камеры'];
    }
}