<?php

/**
 * houses api
 */

namespace api\houses {

    use api\api;
    use Psr\Container\NotFoundExceptionInterface;
    use Selpol\Feature\House\HouseFeature;

    /**
     * entrance method
     */
    class entrance extends api
    {
        public static function GET($params)
        {
            $entranceId = $params['_id'];

            $entrance = container(HouseFeature::class)->getEntrance($entranceId);

            if ($entrance)
                return api::ANSWER($entrance, 'entrance');

            return api::ERROR('Вход не найден');
        }

        public static function POST($params)
        {
            $households = container(HouseFeature::class);

            if (@$params["entranceId"]) {
                $success = $households->addEntrance($params["houseId"], $params["entranceId"], $params["prefix"]);

                return api::ANSWER($success);
            } else {
                $entranceId = $households->createEntrance($params["houseId"], $params["entranceType"], $params["entrance"], $params["lat"], $params["lon"], $params["shared"], $params["plog"], $params["prefix"], $params["callerId"], $params["domophoneId"], $params["domophoneOutput"], $params["cms"], $params["cmsType"], $params["cameraId"], $params["locksDisabled"], $params["cmsLevels"]);

                if ($entranceId)
                    return self::updateIntercom(intval($params["domophoneId"]), $params["cms"], boolval($params["locksDisabled"]) ?? false);

                return api::ANSWER($entranceId, ($entranceId !== false) ? "entranceId" : false);
            }
        }

        public static function PUT($params)
        {
            $households = container(HouseFeature::class);

            $success = $households->modifyEntrance((int)$params["_id"], (int)$params["houseId"], $params["entranceType"], $params["entrance"], $params["lat"], $params["lon"], $params["shared"], $params["plog"], (int)$params["prefix"], $params["callerId"], $params["domophoneId"], $params["domophoneOutput"], $params["cms"], $params["cmsType"], $params["cameraId"], $params["locksDisabled"], $params["cmsLevels"]);

            if ($success)
                return self::updateIntercom(intval($params["domophoneId"]), $params["cms"], boolval($params["locksDisabled"]) ?? false);

            return api::ANSWER($success);
        }

        public static function DELETE($params)
        {
            $households = container(HouseFeature::class);

            if (@$params["houseId"]) {
                $success = $households->deleteEntrance($params["_id"], $params["houseId"]);
            } else {
                $success = $households->destroyEntrance($params["_id"]);
            }

            return api::ANSWER($success);
        }

        public static function index(): array
        {
            return [
                "GET" => "[Дом] Получить вход",
                "POST" => "[Дом] Создать вход",
                "PUT" => "[Дом] Обновить вход",
                "DELETE" => "[Дом] Удалить вход",
            ];
        }

        /**
         * @throws NotFoundExceptionInterface
         */
        private static function updateIntercom(int $id, string $cms, bool $lock): ?array
        {
            $device = intercom($id);

            if (!$device->ping())
                return self::ERROR('Устройство не доступно');

            $device->setCmsModel($cms);
            $device->unlocked($lock);

            return self::ANSWER();
        }
    }
}