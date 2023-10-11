<?php

namespace api\accounts;

use api\api;
use Selpol\Feature\Audit\AuditFeature;

class audit extends api
{
    public static function GET($params)
    {
        $validate = validator($params, [
            'userId' => rule()->id(),

            'auditableId' => rule()->string()->max(1024),
            'auditableType' => rule()->string()->max(1024),

            'eventIp' => rule()->ipV4(),
            'eventType' => rule()->string()->max(1024),
            'eventTarget' => rule()->string()->max(1024),
            'eventCode' => rule()->string()->max(1024),
            'eventMessage' => rule()->string()->max(2048),

            'page' => rule()->int()->clamp(0),
            'size' => rule()->int()->clamp(1, 1000)
        ]);

        $audits = container(AuditFeature::class)->audits($validate['userId'], $validate['auditableId'], $validate['auditableType'], $validate['eventIp'], $validate['eventType'], $validate['eventTarget'], $validate['eventCode'], $validate['eventMessage'], $validate['page'], $validate['size']);

        if ($audits)
            return api::SUCCESS('audits', $audits);

        return api::SUCCESS('audits', []);
    }

    public static function index(): array
    {
        return ['GET' => '[Пользователь] Получить список действий'];
    }
}