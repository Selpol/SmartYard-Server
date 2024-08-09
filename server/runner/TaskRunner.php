<?php declare(strict_types=1);

namespace Selpol\Runner;

use Exception;
use Selpol\Feature\Task\TaskFeature;
use Selpol\Framework\Kernel\Exception\KernelException;
use Selpol\Framework\Kernel\Trait\LoggerKernelTrait;
use Selpol\Framework\Runner\RunnerExceptionHandlerInterface;
use Selpol\Framework\Runner\RunnerInterface;
use Selpol\Service\MqttService;
use Selpol\Service\PrometheusService;
use Selpol\Service\TaskService;
use Selpol\Task\Task;
use Throwable;

class TaskRunner implements RunnerInterface, RunnerExceptionHandlerInterface
{
    use LoggerKernelTrait;

    public function __construct()
    {
        $this->setLogger(file_logger('task'));
    }

    /**
     * @throws Exception
     */
    function run(array $arguments): int
    {
        $arguments = $this->getArguments($arguments);

        $queue = array_key_exists('--queue', $arguments) ? $arguments['--queue'] : 'default';

        $this->registerSignal();
        $this->registerDequeue($queue);

        return 0;
    }

    public function error(Throwable $throwable): int
    {
        $this->logger->error($throwable);

        return 0;
    }

    private function getArguments(array $arguments): array
    {
        $args = [];

        for ($i = 1; $i < count($arguments); $i++) {
            $a = explode('=', $arguments[$i]);

            $args[$a[0]] = @$a[1];
        }

        return $args;
    }

    private function registerSignal(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            sapi_windows_set_ctrl_handler(static function (int $event) {
                if ($event == PHP_WINDOWS_EVENT_CTRL_C)
                    exit(0);
            });
        else {
            pcntl_async_signals(true);

            pcntl_signal(SIGINT, static fn() => exit(0));
            pcntl_signal(SIGTERM, static fn() => exit(0));
        }
    }

    /**
     * @throws Exception
     */
    private function registerDequeue(string $queue): void
    {
        $service = container(TaskService::class);
        $service->setLogger(file_logger('task'));

        $logger = file_logger('task-' . $queue);

        $service->dequeue($queue, static function (Task $task) use ($queue, $logger) {
            $feature = container(TaskFeature::class);
            $service = container(MqttService::class);
            $prometheus = container(PrometheusService::class);

            $counter = $prometheus->getCounter('task', 'count', 'Task count', ['class', 'status']);
            $histogram = $prometheus->getHistogram('task', 'elapsed', 'Task elapsed in milliseconds', ['class', 'status'], [5, 10, 25, 50, 75, 100, 250, 500, 750, 1000, 2500, 5000, 10000, 25000, 50000, 100000]);

            $uuid = guid_v4();

            $time = microtime(true) * 1000;

            try {
                $service->task($uuid, $task->title, 'start', $task->uid, 0);

                $task->setProgressCallback(static fn(int|float $progress) => $service->task($uuid, $task->title, 'progress', $task->uid, $progress));

                $logger->info('Dequeue start task', ['uuid' => $uuid, 'queue' => $queue, 'class' => get_class($task), 'title' => $task->title]);

                $task->onTask();

                $logger->info('Dequeue complete task', ['uuid' => $uuid, 'queue' => $queue, 'class' => get_class($task), 'title' => $task->title, 'elapsed' => (microtime(true) - $time) / 1000]);

                $task->setProgressCallback(null);

                $feature->add($task, 'OK', 1);

                $counter->incBy(1, [$task::class, true]);
                $histogram->observe(microtime(true) * 1000 - $time, [$task::class, true]);

                $service->task($uuid, $task->title, 'done', $task->uid, 'OK');
            } catch (Throwable $throwable) {
                $message = $throwable instanceof KernelException ? $throwable->getLocalizedMessage() : $throwable->getMessage();

                if ($message == '')
                    $message = $throwable->getMessage();

                $service->task($uuid, $task->title, 'done', $task->uid, $message);

                $logger->info('Dequeue error task' . PHP_EOL . $throwable, ['queue' => $queue, 'class' => get_class($task), 'title' => $task->title, 'message' => $message]);

                $task->setProgressCallback(null);

                $feature->add($task, $message, 0);

                try {
                    $task->onError($throwable);
                } catch (Throwable) {
                }

                $counter->incBy(1, [$task::class, false]);
                $histogram->observe(microtime(true) * 1000 - $time, [$task::class, false]);
            } finally {
                $feature->releaseUnique($task);
            }
        });
    }
}