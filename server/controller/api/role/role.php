<?php

namespace api\role;

use api\api;
use Selpol\Feature\Role\RoleFeature;

class role extends api
{
    public static function GET($params)
    {
        return self::SUCCESS('roles', container(RoleFeature::class)->roles());
    }

    public static function POST($params)
    {
        $validate = validator($params, [
            'title' => [filter()->fullSpecialChars(), rule()->required()->string()->max(1024)->nonNullable()],
            'description' => [filter()->fullSpecialChars(), rule()->required()->string()->max(1024)->nonNullable()]
        ]);

        return parent::ANSWER(container(RoleFeature::class)->createRole($validate['title'], $validate['description']));
    }

    public static function PUT($params)
    {
        $validate = validator($params, [
            '_id' => rule()->id(),
            'title' => [filter()->fullSpecialChars(), rule()->required()->string()->max(1024)->nonNullable()],
            'description' => [filter()->fullSpecialChars(), rule()->required()->string()->max(1024)->nonNullable()]
        ]);

        return parent::ANSWER(container(RoleFeature::class)->updateRole($validate['_id'], $validate['title'], $validate['description']));
    }

    public static function DELETE($params)
    {
        $id = rule()->id()->onItem('_id', $params);

        return self::ANSWER(container(RoleFeature::class)->deleteRole($id));
    }

    public static function index(): array|bool
    {
        return [
            'GET' => '[Роль] Получить список',
            'POST' => '[Роль] Создать роль',
            'PUT' => '[Роль] Обновить роль',
            'DELETE' => '[Роль] Удалить роль',
        ];
    }
}