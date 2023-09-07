<?php

namespace Selpol\Task;

use Exception;
use Selpol\Service\TaskService;
use Throwable;

class TaskContainer
{
    private Task $task;

    private ?string $queue = null;
    private ?int $start = null;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function queue(?string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function high(): static
    {
        return $this->queue(TaskService::QUEUE_HIGH);
    }

    public function default(): static
    {
        return $this->queue(TaskService::QUEUE_DEFAULT);
    }

    public function low(): static
    {
        return $this->queue(TaskService::QUEUE_LOW);
    }

    public function delay(?int $start): static
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function sync(): mixed
    {
        return $this->task->onTask();
    }

    public function dispatch(): bool
    {
        $logger = logger('task');
        $queue = $this->queue ?? TaskService::QUEUE_DEFAULT;

        try {
            container(TaskService::class)->enqueue($queue, $this->task, $this->start);

            return true;
        } catch (Throwable $throwable) {
            $logger->error('Error dispatching task' . PHP_EOL . $throwable);

            return false;
        }
    }
}