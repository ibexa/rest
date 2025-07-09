<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedUser value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedUser extends RestUser
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedUser $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        parent::visit($visitor, $generator, $data->user);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_user',
                ['userId' => $data->user->contentInfo->id]
            )
        );
        $visitor->setStatus(201);
    }
}
