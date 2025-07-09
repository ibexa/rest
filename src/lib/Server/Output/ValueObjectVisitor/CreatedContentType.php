<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedContentType value object visitor.
 *
 * @todo coverage add test
 */
class CreatedContentType extends RestContentType
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedContentType $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $restContentType = $data->contentType;

        parent::visit($visitor, $generator, $restContentType);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_content_type' . $this->getUrlTypeSuffix($restContentType->contentType->status),
                [
                    'contentTypeId' => $restContentType->contentType->id,
                ]
            )
        );
        $visitor->setStatus(201);
    }
}
