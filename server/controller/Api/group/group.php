<?php declare(strict_types=1);

namespace Selpol\Controller\Api\group;

use Psr\Http\Message\ResponseInterface;
use Selpol\Controller\Api\Api;
use Selpol\Feature\Group\GroupFeature;

readonly class group extends Api
{
    public static function GET(array $params): array|ResponseInterface
    {
        $validate = validator($params, ['_id' => rule()->required()->string()->nonNullable()]);

        $group = container(GroupFeature::class)->get($validate['_id']);

        if ($group) {
            return self::success($group);
        }

        return self::error('Не удалось найти группу', 404);
    }

    public static function POST(array $params): array|ResponseInterface
    {
        $validate = validator($params, [
            'name' => rule()->required()->string()->nonNullable(),
            'type' => rule()->required()->in(['subscriber', 'camera', 'intercom', 'key', 'address'])->nonNullable(),
            'for' => rule()->required()->in(['contractor', 'address'])->nonNullable(),
            'id' => rule()->required()->nonNullable(),

            'value' => rule()->required()->nonNullable()
        ]);

        $result = container(GroupFeature::class)->insert($validate['name'], GroupFeature::TYPE_MAP[$validate['type']], GroupFeature::FOR_MAP[$validate['for']], $validate['id'], $validate['value']);

        return $result ? self::success($result) : self::error('Не удалось создать группу', 400);
    }

    public static function PUT(array $params): array|ResponseInterface
    {
        $validate = validator($params, [
            '_id' => rule()->required()->string()->nonNullable(),

            'name' => rule()->required()->string()->nonNullable(),
            'type' => rule()->required()->in(['subscriber', 'camera', 'intercom', 'key', 'address'])->nonNullable(),
            'for' => rule()->required()->in(['contractor', 'address'])->nonNullable(),
            'id' => rule()->required()->nonNullable(),

            'value' => rule()->required()->nonNullable()
        ]);

        $result = container(GroupFeature::class)->update($validate['_id'], $validate['name'], GroupFeature::TYPE_MAP[$validate['type']], GroupFeature::FOR_MAP[$validate['for']], $validate['id'], $validate['value']);

        return $result ? self::success() : self::error('Не удалось обновить группу', 400);
    }

    public static function DELETE(array $params): array|ResponseInterface
    {
        $validate = validator($params, ['_id' => rule()->required()->string()->nonNullable()]);

        if (container(GroupFeature::class)->delete($validate['_id'])) {
            return self::success();
        }

        return self::error('Не удалось удалить группуп', 404);
    }

    public static function index(): array
    {
        return ['GET' => '[Deprecated] [Группы] Получить группу', 'POST' => '[Deprecated] [Группы] Создать группу', 'PUT' => '[Deprecated] [Группы] Обновить группу', 'DELETE' => '[Deprecated] [Группы] Удалить группу'];
    }
}