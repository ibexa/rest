<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * CreatedURLWildcard value object visitor.
 *
 * @todo coverage add unit test
 */
class CreatedURLWildcard extends URLWildcard
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\CreatedURLWildcard $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        parent::visit($visitor, $generator, $data->urlWildcard);
        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.load_url_wildcard',
                ['urlWildcardId' => $data->urlWildcard->id]
            )
        );
        $visitor->setStatus(201);
    }
}
