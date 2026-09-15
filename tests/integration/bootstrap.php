<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

use Ibexa\Contracts\Test\Core\Bootstrapper\Bootstrapper;

chdir(dirname(__DIR__, 2));

(new Bootstrapper())->bootstrap(null, [
    Bootstrapper::class => [
        // this package's tests never ran doctrine:schema:update, and Bootstrapper enables it by
        // default; keep it off so the bootstrap stays equivalent
        Bootstrapper::OPTION_SCHEMA_UPDATE => false,
    ],
]);
