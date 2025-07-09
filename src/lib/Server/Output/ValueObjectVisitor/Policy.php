<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\PolicyDraft;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * Policy value object visitor.
 */
class Policy extends AbstractPolicy
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Policy $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('Policy');
        $visitor->setHeader('Content-Type', $generator->getMediaType($data instanceof PolicyDraft ? 'PolicyDraft' : 'Policy'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('PolicyUpdate'));
        $this->visitPolicyAttributes($generator, $data);
        $generator->endObjectElement('Policy');
    }
}
