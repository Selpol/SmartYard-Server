<?php declare(strict_types=1);

namespace Selpol\Entity\Model\Device;

use Selpol\Device\Ip\Camera\CameraModel;
use Selpol\Entity\Repository\Device\DeviceCameraRepository;
use Selpol\Framework\Entity\Entity;
use Selpol\Framework\Entity\Trait\RepositoryTrait;

/**
 * @property int $camera_id
 *
 * @property int|null $dvr_server_id
 * @property int|null $frs_server_id
 *
 * @property int $enabled
 *
 * @property string $model
 * @property string $url
 * @property string|null $stream
 * @property string $credentials
 * @property string|null $name
 * @property string|null $dvr_stream
 * @property string|null $timezone
 *
 * @property double|null $lat
 * @property double|null $lon
 *
 * @property double|null $direction
 * @property double|null $angle
 * @property double|null $distance
 * @property int|null $md_left
 * @property int|null $md_top
 * @property int|null $md_width
 * @property int|null $md_height
 *
 * @property int|null $common
 *
 * @property string|null $ip
 *
 * @property string|null $comment
 */
class DeviceCamera extends Entity
{
    /**
     * @use RepositoryTrait<DeviceCameraRepository>
     */
    use RepositoryTrait;

    public static string $table = 'cameras';

    public static string $columnId = 'camera_id';

    public static function getColumns(): array
    {
        return [
            static::$columnId => rule()->id(),

            'dvr_server_id' => rule()->int()->clamp(0),
            'frs_server_id' => rule()->int()->clamp(0),

            'enabled' => rule()->required()->int()->nonNullable(),

            'model' => rule()->required()->in(array_keys(CameraModel::models()))->nonNullable(),
            'url' => rule()->required()->url()->nonNullable(),
            'stream' => rule()->string(),
            'credentials' => rule()->required()->string()->nonNullable(),
            'name' => rule()->string(),
            'dvr_stream' => rule()->string(),
            'timezone' => rule()->string(),

            'lat' => rule()->float(),
            'lon' => rule()->float(),

            'direction' => rule()->float(),
            'angle' => rule()->float(),
            'distance' => rule()->float(),

            'md_left' => rule()->int(),
            'md_top' => rule()->int(),
            'md_width' => rule()->int(),
            'md_height' => rule()->int(),

            'common' => rule()->int(),

            'ip' => rule()->ipV4(),

            'comment' => rule()->string()
        ];
    }
}