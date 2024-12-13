<?php declare(strict_types=1);

namespace Selpol\Controller\Request\Internal;

use Selpol\Framework\Router\Route\RouteRequest;

/**
 * @property-read int $date Дата события
 * @property-read string $ip IP-Адрес устройства
 *
 * @property-read null|int $callId Номер звонящего
 */
readonly class ActionCallFinishedRequest extends RouteRequest
{
    public static function getValidate(): array
    {
        return [
            'ip' => rule()->required()->ipV4()->nonNullable(),

            'callId' => [filter()->default(0), rule()->int()],

            'date' => rule()->required()->nonNullable()
        ];
    }
}