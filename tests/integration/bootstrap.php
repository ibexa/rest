<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Contracts\Test\Core\Bootstrapper\Bootstrapper;
use Ibexa\Contracts\Test\Core\Bootstrapper\DatabaseSchemaHook;
use Ibexa\Contracts\Test\Core\Bootstrapper\FixtureHook;

chdir(dirname(__DIR__, 2));

(new Bootstrapper())->bootstrap(null, [
    Bootstrapper::class => [Bootstrapper::OPTION_PREPARE_DATABASE => false],
    DatabaseSchemaHook::class => [DatabaseSchemaHook::OPTION_LOAD_SCHEMA => false],
    FixtureHook::class => [FixtureHook::OPTION_LOAD_FIXTURES => false],
]);
