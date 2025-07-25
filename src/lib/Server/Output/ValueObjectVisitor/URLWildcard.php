<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard as URLWildcardValue;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * URLWildcard value object visitor.
 */
class URLWildcard extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $visitor->setHeader('Content-Type', $generator->getMediaType('UrlWildcard'));
        $generator->startObjectElement('UrlWildcard');
        $this->visitURLWildcardAttributes($generator, $data);
        $generator->endObjectElement('UrlWildcard');
    }

    protected function visitURLWildcardAttributes(Generator $generator, URLWildcardValue $data): void
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_url_wildcard', ['urlWildcardId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $data->id);
        $generator->endAttribute('id');

        $generator->startValueElement('sourceUrl', $data->sourceUrl);
        $generator->endValueElement('sourceUrl');

        $generator->startValueElement('destinationUrl', $data->destinationUrl);
        $generator->endValueElement('destinationUrl');

        $generator->startValueElement(
            'forward',
            $this->serializeBool($generator, $data->forward)
        );
        $generator->endValueElement('forward');
    }
}
