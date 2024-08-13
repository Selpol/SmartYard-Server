<?php declare(strict_types=1);

namespace Selpol\Task\Tasks\Intercom;

use RuntimeException;
use Selpol\Device\Exception\DeviceException;
use Selpol\Device\Ip\Intercom\IntercomModel;
use Selpol\Device\Ip\Intercom\Setting\Cms\CmsInterface;
use Selpol\Device\Ip\Intercom\Setting\Cms\CmsLevels;
use Selpol\Entity\Model\Device\DeviceIntercom;
use Selpol\Feature\House\HouseFeature;
use Selpol\Service\DeviceService;
use Throwable;

class IntercomLevelTask extends IntercomTask
{
    public function __construct(int $id)
    {
        parent::__construct($id, 'Синхронизация уровней домофона (' . $id . ')');
    }

    public function onTask(): bool
    {
        $households = container(HouseFeature::class);

        $deviceIntercom = DeviceIntercom::findById($this->id, setting: setting()->nonNullable());
        $deviceModel = IntercomModel::model($deviceIntercom->model);

        if (!$deviceIntercom || !$deviceModel) {
            $this->logger?->debug('Domophone not found', ['id' => $this->id]);

            return false;
        }

        $this->setProgress(1);

        $entrances = $households->getEntrances('domophoneId', ['domophoneId' => $this->id, 'output' => '0']);

        if (!$entrances) {
            $this->logger?->debug('This domophone is not linked with any entrance', ['id' => $this->id]);

            return false;
        }

        $this->setProgress(2);

        try {
            $device = container(DeviceService::class)->intercom($deviceIntercom->model, $deviceIntercom->url, $deviceIntercom->credentials);

            if (!$device) {
                return false;
            }

            if (!$device->ping()) {
                throw new DeviceException($device, 'Устройство не доступно');
            }

            if (!$device instanceof CmsInterface) {
                return false;
            }

            $cms_levels = array_map('intval', explode(',', $entrances[0]['cmsLevels']));

            $device->setCmsLevels(new CmsLevels($cms_levels));

            return true;
        } catch (Throwable $throwable) {
            $this->logger?->error($throwable, ['id' => $this->id]);

            throw new RuntimeException($throwable->getMessage(), previous: $throwable);
        }
    }
}