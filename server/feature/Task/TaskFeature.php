<?php

namespace Selpol\Feature\Task;

use Selpol\Feature\Feature;
use Selpol\Task\Task;

abstract class TaskFeature extends Feature
{
    /**
     * @param int $size
     * @param int $page
     * @return array<\Selpol\Entity\Model\Task>
     */
    public abstract function page(int $size, int $page): array;

    public abstract function add(Task $task, string $message, int $status): void;

    public abstract function dispatch(int $id): bool;
}