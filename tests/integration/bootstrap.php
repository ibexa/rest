<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Tests\Integration\Rest\IbexaTestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;

chdir(dirname(__DIR__, 2));

$kernel = new IbexaTestKernel('test', true);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);

// Skipping database initialization until really needed by integration tests

$kernel->shutdown();
