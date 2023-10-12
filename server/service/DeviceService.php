<?php declare(strict_types=1);

namespace Selpol\Service;

use Selpol\Device\Ip\Camera\CameraDevice;
use Selpol\Device\Ip\Camera\CameraModel;
use Selpol\Device\Ip\Intercom\IntercomDevice;
use Selpol\Device\Ip\Intercom\IntercomModel;
use Selpol\Feature\Camera\CameraFeature;
use Selpol\Feature\House\HouseFeature;
use Selpol\Framework\Container\Attribute\Singleton;
use Selpol\Http\Uri;

#[Singleton]
class DeviceService
{
    public function cameraById(int $id): ?CameraDevice
    {
        if ($camera = container(CameraFeature::class)->getCamera($id))
            return $this->camera($camera['model'], $camera['url'], $camera['credentials']);

        return null;
    }

    public function camera(string $model, string $url, string $password): ?CameraDevice
    {
        $models = CameraModel::models();

        if (array_key_exists($model, $models))
            return new $models[$model]->class(new Uri($url), $password, $models[$model]);

        return null;
    }

    public function intercomById(int $id): ?IntercomDevice
    {
        if ($domophone = container(HouseFeature::class)->getDomophone($id))
            return $this->intercom($domophone['model'], $domophone['url'], $domophone['credentials']);

        return null;
    }

    public function intercom(string $model, string $url, string $password): ?IntercomDevice
    {
        $models = IntercomModel::models();

        if (array_key_exists($model, $models))
            return new $models[$model]->class(new Uri($url), $password, $models[$model]);

        return null;
    }
}