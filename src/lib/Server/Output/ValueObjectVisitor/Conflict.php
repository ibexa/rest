<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * Conflict Value object visitor.
 */
class Conflict extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $visitor->setStatus(409);
    }
}
