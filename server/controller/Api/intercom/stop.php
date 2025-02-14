<?php declare(strict_types=1);

namespace Selpol\Controller\Api\intercom;

use Selpol\Device\Ip\Intercom\IntercomDevice;
use Psr\Http\Message\ResponseInterface;
use Selpol\Controller\Api\Api;

readonly class stop extends Api
{
    public static function GET(array $params): ResponseInterface
    {
        $device = intercom(rule()->id()->onItem('_id', $params));

        if ($device instanceof IntercomDevice) {
            if (!$device->ping()) {
                return self::error('Устройство не доступно', 404);
            }

            $device->callStop();

            return self::success();
        }

        return self::error('Домофон не найден', 404);
    }

    public static function index(): array|bool
    {
        return ['GET' => '[Deprecated] [Домофон] Сбросить активные звонки'];
    }
}