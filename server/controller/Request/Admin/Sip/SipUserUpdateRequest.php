<?php declare(strict_types=1);

namespace Selpol\Controller\Request\Admin\Sip;

use Selpol\Framework\Router\Route\RouteRequest;

/**
 * @property-read int $id Идентификатор аккаунтп
 *
 * @property-read int $type Префикс номера
 * @property-read string $title Имя аккаунта
 *
 * @property-read string $password Пароль аккаунта
 */
readonly class SipUserUpdateRequest extends RouteRequest
{
    public static function getValidate(): array
    {
        return [
            'id' => rule()->id(),

            'type' => rule()->required()->int()->clamp(1, 9)->nonNullable(),
            'title' => rule()->required()->string()->nonNullable(),

            'password' => rule()->required()->string()->nonNullable()
        ];
    }
}