<?php declare(strict_types=1);

namespace Selpol\Controller\Api\intercom;

use Selpol\Controller\Api\Api;
use Selpol\Framework\Http\Response;

readonly class stop extends Api
{
    public static function GET(array $params): array|Response
    {
        $device = intercom(rule()->id()->onItem('_id', $params));

        if ($device) {
            if (!$device->ping())
                return self::ERROR('Устройство не доступно');

            $device->callStop();

            return self::ANSWER();
        }

        return self::ERROR('Домофон не найден');
    }

    public static function index(): array|bool
    {
        return ['GET' => '[Домофон] Сбросить активные звонки'];
    }
}