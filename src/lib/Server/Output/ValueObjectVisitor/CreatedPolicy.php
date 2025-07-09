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
 * CreatedPolicy value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedPolicy extends AbstractPolicy
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedPolicy $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('Policy');
        $visitor->setHeader(
            'Content-Type',
            $generator->getMediaType(
            $data->policy instanceof PolicyDraft ? 'PolicyDraft' : 'Policy'
        ),
        );
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('PolicyUpdate'));
        $this->visitPolicyAttributes($generator, $data->policy);
        $generator->endObjectElement('Policy');

        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_policy',
                [
                    'roleId' => $data->policy->roleId,
                    'policyId' => $data->policy->id,
                ]
            )
        );
        $visitor->setStatus(201);
    }
}
