<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Thumbnail;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Output\FieldTypeSerializer;
use Ibexa\Rest\Server\Values\RelationList as RelationListValue;
use Ibexa\Rest\Server\Values\Version as VersionValue;

/**
 * Version value object visitor.
 */
class Version extends ValueObjectVisitor
{
    protected FieldTypeSerializer $fieldTypeSerializer;

    public function __construct(FieldTypeSerializer $fieldTypeSerializer)
    {
        $this->fieldTypeSerializer = $fieldTypeSerializer;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\Version $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Content $content */
        $content = $data->content;

        $generator->startObjectElement('Version');

        $visitor->setHeader('Content-Type', $generator->getMediaType('Version'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('VersionUpdate'));

        $this->visitVersionAttributes($visitor, $generator, $data);
        $this->visitThumbnail($generator, $content->getThumbnail());

        $generator->endObjectElement('Version');
    }

    protected function visitVersionAttributes(Visitor $visitor, Generator $generator, VersionValue $data): void
    {
        $content = $data->content;

        $versionInfo = $content->getVersionInfo();

        $path = $data->path;
        if ($path == null) {
            $path = $this->router->generate(
                'ibexa.rest.load_content_in_version',
                [
                    'contentId' => $content->id,
                    'versionNumber' => $versionInfo->versionNo,
                ]
            );
        }

        $generator->startAttribute('href', $path);
        $generator->endAttribute('href');

        $visitor->visitValueObject($versionInfo);

        $generator->startHashElement('Fields');
        $generator->startList('field');
        foreach ($content->getFields() as $field) {
            $visitor->visitValueObject($field);
        }
        $generator->endList('field');
        $generator->endHashElement('Fields');

        $visitor->visitValueObject(
            new RelationListValue(
                $data->relations,
                $content->id,
                $versionInfo->versionNo
            )
        );
    }

    private function visitThumbnail(
        Generator $generator,
        ?Thumbnail $thumbnail
    ): void {
        $generator->startObjectElement('Thumbnail');

        if (!empty($thumbnail->resource)) {
            $generator->startValueElement('resource', $thumbnail->resource);
            $generator->endValueElement('resource');

            $generator->startValueElement('width', $thumbnail->width);
            $generator->endValueElement('width');

            $generator->startValueElement('height', $thumbnail->height);
            $generator->endValueElement('height');

            $generator->startValueElement('mimeType', $thumbnail->mimeType);
            $generator->endValueElement('mimeType');
        }

        $generator->endObjectElement('Thumbnail');
    }
}
