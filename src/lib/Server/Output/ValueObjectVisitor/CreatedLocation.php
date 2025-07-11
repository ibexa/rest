<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedLocation value object visitor.
 *
 * @todo coverage add test
 */
class CreatedLocation extends RestLocation
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedLocation $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        parent::visit($visitor, $generator, $data->restLocation);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_location',
                ['locationPath' => trim($data->restLocation->location->pathString, '/')]
            )
        );
        $visitor->setStatus(201);
    }
}
