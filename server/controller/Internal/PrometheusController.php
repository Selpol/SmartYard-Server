<?php declare(strict_types=1);

namespace Selpol\Controller\Internal;

use Psr\Container\NotFoundExceptionInterface;
use RedisException;
use RuntimeException;
use Selpol\Controller\RbtController;
use Selpol\Feature\File\FileFeature;
use Selpol\Framework\Http\Response;
use Selpol\Framework\Router\Attribute\Controller;
use Selpol\Framework\Router\Attribute\Method\Get;
use Selpol\Service\DatabaseService;
use Selpol\Service\Prometheus\Metric;
use Selpol\Service\Prometheus\Sample;
use Selpol\Service\PrometheusService;

/**
 * Метрики
 */
#[Controller('/internal/prometheus')]
readonly class PrometheusController extends RbtController
{
    /**
     * @throws NotFoundExceptionInterface
     */
    #[Get]
    public function index(): Response
    {
        $service = container(PrometheusService::class);

        $metrics = $service->collect();

        $result = [];

        foreach ($metrics as $metric) {
            $result[] = '# HELP smartyard_' . $metric->name . ' ' . $metric->help;
            $result[] = '# TYPE smartyard_' . $metric->name . ' ' . $metric->type;

            foreach ($metric->samples as $sample) {
                $result[] = $this->renderSample($metric, $sample);
            }
        }

        $this->memory($result);
        $this->memory_peak($result);

        $this->disk_free_space($result, '/srv/app');
        $this->disk_free_space($result, '/srv/app/var/log');

        $this->disk_total_space($result, '/srv/app');
        $this->disk_total_space($result, '/srv/app/var/log');

        $counts = container(DatabaseService::class)->get('SELECT (SELECT COUNT(*) FROM addresses_houses) AS HOUSES, (SELECT COUNT(*) FROM houses_flats) AS FLATS, (SELECT COUNT(*) FROM houses_subscribers_mobile) AS SUBSCRIBERS, (SELECT COUNT(*) FROM houses_domophones) AS INTERCOMS, (SELECT COUNT(*) FROM cameras) AS CAMERAS, (SELECT COUNT(*) FROM houses_rfids) AS KEYS, (SELECT COUNT(*) FROM flat_block) AS FLAT_BLOCKS, (SELECT COUNT(*) FROM subscriber_block) AS SUBSCRIBER_BLOCKS, (SELECT last_value FROM task_id_seq) AS TASKS');

        if ($counts && count($counts) == 1) {
            $this->houseCount($result, $counts[0]['houses']);
            $this->flatCount($result, $counts[0]['flats']);
            $this->subscriberCount($result, $counts[0]['subscribers']);
            $this->intercomCount($result, $counts[0]['intercoms']);
            $this->cameraCount($result, $counts[0]['cameras']);
            $this->keyCount($result, $counts[0]['keys']);
            $this->flatBlock($result, $counts[0]['flat_blocks']);
            $this->subscriberBlock($result, $counts[0]['subscriber_blocks']);
            $this->taskCount($result, $counts[0]['tasks']);
        }

        return response()
            ->withHeader('Content-Type', 'text/plain; version=0.0.4')
            ->withBody(stream(implode(PHP_EOL, $result) . PHP_EOL));
    }

    private function memory(array &$result): void
    {
        $result[] = '# HELP smartyard_php_memory PHP Memory usage';
        $result[] = '# TYPE smartyard_php_memory gauge';
        $result[] = 'smartyard_php_memory{} ' . memory_get_usage();
    }

    private function memory_peak(array &$result): void
    {
        $result[] = '# HELP smartyard_php_memory_peak PHP Memory peak usage';
        $result[] = '# TYPE smartyard_php_memory_peak gauge';
        $result[] = 'smartyard_php_memory_peak{} ' . memory_get_peak_usage();
    }

    private function disk_free_space(array &$result, string $directory): void
    {
        $value = disk_free_space($directory);

        if (is_float($value)) {
            $result[] = '# HELP smartyard_disk_free_space Disk free space';
            $result[] = '# TYPE smartyard_disk_free_space gauge';
            $result[] = 'smartyard_disk_free_space{directory="' . $directory . '"} ' . $value;
        }
    }

    private function disk_total_space(array &$result, string $directory): void
    {
        $value = disk_total_space($directory);

        if (is_float($value)) {
            $result[] = '# HELP smartyard_disk_total_space Disk total space';
            $result[] = '# TYPE smartyard_disk_total_space gauge';
            $result[] = 'smartyard_disk_total_space{directory="' . $directory . '"} ' . $value;
        }
    }

    private function houseCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_house_count House count';
        $result[] = '# TYPE smartyard_house_count gauge';
        $result[] = 'smartyard_house_count{} ' . $value;
    }

    private function flatCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_flat_count Flat count';
        $result[] = '# TYPE smartyard_flat_count gauge';
        $result[] = 'smartyard_flat_count{} ' . $value;
    }

    private function subscriberCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_subscriber_count Subscriber count';
        $result[] = '# TYPE smartyard_subscriber_count gauge';
        $result[] = 'smartyard_subscriber_count{} ' . $value;
    }

    private function intercomCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_intercom_count Intercom count';
        $result[] = '# TYPE smartyard_intercom_count gauge';
        $result[] = 'smartyard_intercom_count{} ' . $value;
    }

    private function cameraCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_camera_count Camera count';
        $result[] = '# TYPE smartyard_camera_count gauge';
        $result[] = 'smartyard_camera_count{} ' . $value;
    }

    private function keyCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_key_count Key count';
        $result[] = '# TYPE smartyard_key_count gauge';
        $result[] = 'smartyard_key_count{} ' . $value;
    }

    private function flatBlock(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_flat_block_count Flat block count';
        $result[] = '# TYPE smartyard_flat_block_count gauge';
        $result[] = 'smartyard_flat_block_count{} ' . $value;
    }

    private function subscriberBlock(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_subscriber_block_count Subscriber block count';
        $result[] = '# TYPE smartyard_subscriber_block_count gauge';
        $result[] = 'smartyard_subscriber_block_count{} ' . $value;
    }

    private function taskCount(array &$result, int $value): void
    {
        $result[] = '# HELP smartyard_task_total_count Task total block count';
        $result[] = '# TYPE smartyard_task_total_count gauge';
        $result[] = 'smartyard_task_total_count{} ' . $value;
    }

    private function renderSample(Metric $metric, Sample $sample): string
    {
        $labelNames = $metric->labelNames;

        if ($metric->labelNames !== [] || $sample->labelNames !== []) {
            $escapedLabels = $this->escapeAllLabels($labelNames, $sample);

            return 'smartyard_' . $sample->name . '{' . implode(',', $escapedLabels) . '} ' . $sample->value;
        }

        return 'smartyard_' . $sample->name . ' ' . $sample->value;
    }

    private function escapeLabelValue(string $v): string
    {
        return str_replace(["\\", "\n", '"'], ["\\\\", "\\n", "\\\""], $v);
    }

    private function escapeAllLabels(array $labelNames, Sample $sample): array
    {
        $escapedLabels = [];

        $labels = array_combine(array_merge($labelNames, $sample->labelNames), $sample->labelValues);

        if ($labels === false) {
            throw new RuntimeException('Unable to combine labels.');
        }

        foreach ($labels as $labelName => $labelValue) {
            $escapedLabels[] = $labelName . '="' . $this->escapeLabelValue((string)$labelValue) . '"';
        }

        return $escapedLabels;
    }
}