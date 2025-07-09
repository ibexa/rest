<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedContent value object visitor.
 */
class CreatedContent extends RestContent
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedContent $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        parent::visit($visitor, $generator, $data->content);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $data->content->contentInfo->id]
            )
        );
        $visitor->setStatus(201);
    }
}
