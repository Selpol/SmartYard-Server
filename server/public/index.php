<?php

use Selpol\Kernel\Kernel;
use Selpol\Kernel\Runner\RouterRunner;
use Selpol\Router\Router;

require_once dirname(__FILE__, 2) . '/vendor/autoload.php';

exit((new Kernel())->setRunner(new RouterRunner(new Router()))->bootstrap()->run());