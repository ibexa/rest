<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedPolicy value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedPolicy extends Policy
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Server\Values\CreatedPolicy $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->policy);
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

class_alias(CreatedPolicy::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\CreatedPolicy');
