<?php declare(strict_types=1);

namespace Selpol\Task\Tasks\Intercom;

use Selpol\Task\Task;

abstract class IntercomTask extends Task
{
    /** @var int Идентификатор устройства */
    public int $id;

    protected function __construct(int $id, string $title)
    {
        parent::__construct($title);

        $this->id = $id;

        $this->setLogger(file_logger('task-intercom'));
    }
}