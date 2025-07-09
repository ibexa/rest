<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedVersion value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedVersion extends Version
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedVersion $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        parent::visit($visitor, $generator, $data->version);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_content_in_version',
                [
                    'contentId' => $data->version->content->id,
                    'versionNumber' => $data->version->content->getVersionInfo()->versionNo,
                ]
            )
        );
        $visitor->setStatus(201);
    }
}
