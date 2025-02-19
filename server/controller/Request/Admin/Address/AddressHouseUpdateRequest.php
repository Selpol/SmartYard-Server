<?php declare(strict_types=1);

namespace Selpol\Controller\Request\Admin\Address;

use Selpol\Framework\Router\Route\RouteRequest;

/**
 * @property-read int $id
 * 
 * @property-read int|null $address_settlement_id
 * @property-read int|null $address_street_id
 * 
 * @property-read string|null $house_uuid
 * @property-read string $house_type
 * @property-read string|null $house_type_full
 * @property-read string|null $house_full
 * @property-read string $house
 * 
 * @property-read string|null $timezone
 */
readonly class AddressHouseUpdateRequest extends RouteRequest
{
    public static function getValidate(): array
    {
        return [
            'id' => rule()->id(),

            'address_settlement_id' => rule()->int()->clamp(0),
            'address_street_id' => rule()->int()->clamp(0),

            'house_uuid' => rule()->uuid(),
            'house_type' => rule()->required()->string()->nonNullable(),
            'house_type_full' => rule()->string(),
            'house_full' => rule()->required()->string()->nonNullable(),
            'house' => rule()->required()->string()->nonNullable(),

            'timezone' => rule()->string()
        ];
    }
}