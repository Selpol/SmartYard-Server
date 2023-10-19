<?php declare(strict_types=1);

namespace Selpol\Task\Tasks\Intercom\Cms;

use RuntimeException;
use Selpol\Feature\House\HouseFeature;
use Selpol\Http\Exception\HttpException;
use Selpol\Task\Task;
use Selpol\Task\TaskUnique;
use Selpol\Task\TaskUniqueInterface;
use Throwable;

class IntercomSyncCmsTask extends Task implements TaskUniqueInterface
{
    public int $entranceId;

    public function __construct(int $entranceId)
    {
        parent::__construct('Конфигурация CMS (' . $entranceId . ')');

        $this->entranceId = $entranceId;
    }

    public function unique(): TaskUnique
    {
        return new TaskUnique([IntercomSyncCmsTask::class, $this->entranceId], 3600);
    }

    public function onTask(): bool
    {
        $entrance = container(HouseFeature::class)->getEntrance($this->entranceId);

        if (!$entrance || $entrance['shared'])
            return false;

        $this->cms($entrance, $entrance['domophoneId']);

        return true;
    }

    private function cms(array $entrance, int $id): void
    {
        try {
            $device = intercom($id);

            if (!$device->ping())
                throw new HttpException(message: 'Устройство не доступно');

            $cms_allocation = container(HouseFeature::class)->getCms($entrance['entranceId']);

            foreach ($cms_allocation as $item)
                $device->addCmsDeffer($item['cms'] + 1, intval($item['dozen']), intval($item['unit']), intval($item['apartment']));

            $device->deffer();
        } catch (Throwable $throwable) {
            if ($throwable instanceof HttpException)
                throw $throwable;

            file_logger('intercom')->error($throwable);

            throw new RuntimeException($throwable->getMessage(), previous: $throwable);
        }
    }
}