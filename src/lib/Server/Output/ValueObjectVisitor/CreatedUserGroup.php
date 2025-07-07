<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedUserGroup value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedUserGroup extends RestUserGroup
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedUserGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        parent::visit($visitor, $generator, $data->userGroup);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_user_group',
                ['groupPath' => trim($data->userGroup->mainLocation->pathString, '/')]
            )
        );
        $visitor->setStatus(201);
    }
}
