<?php

namespace Selpol\Task\Tasks\Migration;

use RuntimeException;
use Selpol\Service\DatabaseService;
use Throwable;

class MigrationDownTask extends MigrationTask
{
    public function __construct(int $dbVersion, ?int $version, bool $force)
    {
        parent::__construct('Понижение версии базы данных (' . $dbVersion . ', ' . $version . ')', $dbVersion, $version, $force);
    }

    public function onTask(): bool
    {
        if ($this->version && $this->dbVersion <= $this->version) {
            return true;
        }

        $migrations = array_reverse($this->getMigration('down', $this->version, $this->dbVersion + 1), true);

        $db = container(DatabaseService::class);

        $db->getConnection()->beginTransaction();

        foreach ($migrations as $migrationVersion => $migrationValues) {
            try {
                $migrationValues = array_reverse($migrationValues, true);

                foreach ($migrationValues as $migrationStep) {
                    $sql = trim(file_get_contents(path('migration/pgsql/down/' . $migrationStep)));

                    $db->getConnection()->exec($sql);
                }
            } catch (Throwable $throwable) {
                if (!$this->force) {
                    $db->getConnection()->rollBack();

                    throw new RuntimeException($throwable->getMessage(), previous: $throwable);
                }
            }

            if ($this->version > 0) {
                $db->modify("UPDATE core_vars SET var_value = :version WHERE var_name = 'database.version'", ['version' => $migrationVersion - 1]);
            }
        }

        return $db->getConnection()->commit();
    }
}