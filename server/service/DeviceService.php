<?php declare(strict_types=1);

namespace Selpol\Service;

use Selpol\Device\Ip\Camera\CameraDevice;
use Selpol\Device\Ip\Camera\CameraModel;
use Selpol\Device\Ip\Dvr\DvrDevice;
use Selpol\Device\Ip\Dvr\DvrModel;
use Selpol\Device\Ip\Intercom\IntercomConfigResolver;
use Selpol\Device\Ip\Intercom\IntercomDevice;
use Selpol\Device\Ip\Intercom\IntercomModel;
use Selpol\Entity\Model\Device\DeviceCamera;
use Selpol\Entity\Model\Device\DeviceIntercom;
use Selpol\Entity\Model\Dvr\DvrServer;
use Selpol\Feature\Config\Config;
use Selpol\Feature\Config\ConfigFeature;
use Selpol\Feature\Config\ConfigResolver;
use Selpol\Framework\Cache\FileCache;
use Selpol\Framework\Container\Attribute\Singleton;
use Selpol\Framework\Http\Uri;
use Throwable;

#[Singleton]
class DeviceService
{
    /** @var array<int, CameraDevice> */
    private array $cameras = [];

    /** @var array<int, IntercomDevice> */
    private array $intercoms = [];

    /** @var array<int, DvrDevice> */
    private array $dvrs = [];

    private bool $cache = true;

    public function __construct()
    {
    }

    public function disableCache(): void
    {
        $this->cache = false;
    }

    public function cameraById(int $id): ?CameraDevice
    {
        if ($this->cache && array_key_exists($id, $this->cameras)) {
            return $this->cameras[$id];
        }

        if (($camera = DeviceCamera::findById($id, setting: setting()->columns(['camera_id', 'model', 'url', 'credentials'])->nonNullable())) instanceof DeviceCamera) {
            return $this->cameraByEntity($camera);
        }

        return null;
    }

    public function cameraByEntity(DeviceCamera $camera): ?CameraDevice
    {
        $id = $camera->camera_id;

        if ($this->cache && array_key_exists($id, $this->cameras)) {
            return $this->cameras[$id];
        }

        if (($model = CameraModel::model($camera->model)) instanceof CameraModel) {
            $camera = new $model->class(new Uri($camera->url), $camera->credentials, $model);
            $this->cameras[$id] = $camera;

            return $camera;
        }

        return null;
    }

    public function intercomById(int $id): ?IntercomDevice
    {
        if ($this->cache && array_key_exists($id, $this->intercoms)) {
            return $this->intercoms[$id];
        }

        if (($intercom = DeviceIntercom::findById($id, setting: setting()->columns(['house_domophone_id', 'model', 'url', 'credentials'])->nonNullable())) instanceof DeviceIntercom) {
            return $this->intercomByEntity($intercom);
        }

        return null;
    }

    public function intercomByEntity(DeviceIntercom $intercom): ?IntercomDevice
    {
        $id = $intercom->house_domophone_id;

        if ($this->cache && array_key_exists($id, $this->intercoms)) {
            return $this->intercoms[$id];
        }

        if (($model = IntercomModel::model($intercom->model)) instanceof IntercomModel) {
            $optimize = false;

            if ($this->cache) {
                try {
                    $values = container(FileCache::class)->get('intercom.config.' . $intercom->house_domophone_id);

                    if ($values) {
                        $config = new Config($values);
                    } else {
                        $config = container(ConfigFeature::class)->getConfigForIntercom($model->config, $intercom->config);

                        $values = $config->getValues();
                        $keys = array_keys($values);

                        $result = [];

                        $filter = function (string $value) use (&$result, $values, &$keys) {
                            $length = strlen($value);

                            foreach ($keys as $key) {
                                if (!str_starts_with($value, $key)) {
                                    continue;
                                }

                                $result[substr($key, $length)] = $values[$key];

                                unset($keys[$key]);
                            }
                        };

                        $filter('intercom.' . $model->vendor . '.' . str_replace('.', '', $intercom->device_model) . '.');

                        if (str_contains($intercom->device_model, '_rev')) {
                            $segments = explode('_rev', $intercom->device_model);

                            $filter('intercom.' . $model->vendor . '.' . str_replace('.', '', $segments[0]) . '.');
                        }

                        $filter('intercom.' . $model->vendor . '.' . str_replace('.', '', $model->title) . '.');
                        $filter('intercom.' . $model->vendor . '.');
                        $filter('intercom.');

                        $config = new Config($result);

                        $optimize = true;
                    }

                    $resolver = new ConfigResolver($config);
                } catch (Throwable $throwable) {
                    file_logger('intercom')->error($throwable);
                }
            }

            if (!isset($resolver)) {
                $config = container(ConfigFeature::class)->getConfigForIntercom($model->config, $intercom->config);
                $resolver = new IntercomConfigResolver($config, $model, $intercom);
            }

            /** @var IntercomDevice $device */
            $device = new $model->class(new Uri($intercom->url), $intercom->credentials, $model, $intercom, $resolver);

            if ($this->cache) {
                if ($optimize) {
                    try {
                        container(FileCache::class)->set('intercom.config.' . $intercom->house_domophone_id, $device->resolver->config->getValues());
                    } catch (Throwable $throwable) {
                        file_logger('intercom')->error($throwable);
                    }
                }

                $this->intercoms[$id] = $device;
            }

            return $device;
        }

        return null;
    }

    public function dvrById(int $id): ?DvrDevice
    {
        if ($this->cache && array_key_exists($id, $this->dvrs)) {
            return $this->dvrs[$id];
        }

        if (($server = DvrServer::findById($id, setting: setting()->nonNullable())) instanceof DvrServer) {
            return $this->dvrByEntity($server);
        }

        return null;
    }

    public function dvrByEntity(DvrServer $server): ?DvrDevice
    {
        $id = $server->id;

        if ($this->cache && array_key_exists($id, $this->dvrs)) {
            return $this->dvrs[$id];
        }

        if (!$server->credentials) {
            return null;
        }

        $credentials = $server->credentials();

        if (($model = DvrModel::model($server->type)) instanceof DvrModel) {
            $dvr = new $model->class(new Uri($server->url), $credentials['username'], $credentials['password'], $model, $server);

            if ($this->cache) {
                $this->dvrs[$id] = $dvr;
            }

            return $dvr;
        }

        return null;
    }
}