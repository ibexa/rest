<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\Base\Exceptions\BadStateException as CoreBadStateException;
use Ibexa\Core\Helper\TranslationHelper;
use Ibexa\Rest\Server\Values\Version as VersionValue;

/**
 * RestContent value object visitor.
 */
class RestContent extends ValueObjectVisitor
{
    /** @var \Ibexa\Core\Helper\TranslationHelper */
    private $translationHelper;

    public function __construct(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Server\Values\RestContent $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $restContent = $data;
        $contentInfo = $restContent->contentInfo;
        $translatedContentName = $this->translationHelper->getTranslatedContentNameByContentInfo($contentInfo);
        $contentType = $restContent->contentType;
        $mainLocation = $restContent->mainLocation;
        $currentVersion = $restContent->currentVersion;

        $mediaType = ($restContent->currentVersion === null ? 'ContentInfo' : 'Content');

        $generator->startObjectElement('Content', $mediaType);

        $visitor->setHeader('Content-Type', $generator->getMediaType($mediaType));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('ContentUpdate'));

        $generator->attribute(
            'href',
            $data->path ?? $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $contentInfo->id]
            )
        );

        $generator->attribute('remoteId', $contentInfo->remoteId);
        $generator->attribute('id', $contentInfo->id);

        $generator->startObjectElement('ContentType');
        $generator->attribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content_type',
                ['contentTypeId' => $contentInfo->contentTypeId]
            )
        );
        $generator->endObjectElement('ContentType');

        $generator->valueElement('Name', $contentInfo->name);

        $generator->valueElement('TranslatedName', $translatedContentName);

        $generator->startObjectElement('Versions', 'VersionList');
        $generator->attribute(
            'href',
            $this->router->generate('ibexa.rest.load_content_versions', ['contentId' => $contentInfo->id])
        );
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('CurrentVersion', 'Version');
        $generator->attribute(
            'href',
            $this->router->generate(
                'ibexa.rest.redirect_current_version',
                ['contentId' => $contentInfo->id]
            )
        );

        // Embed current version, if available
        if ($currentVersion !== null) {
            $visitor->visitValueObject(
                new VersionValue(
                    $currentVersion,
                    $contentType,
                    $restContent->relations
                )
            );
        }

        $generator->endObjectElement('CurrentVersion');

        $generator->startObjectElement('Section');
        $generator->attribute(
            'href',
            $this->router->generate('ibexa.rest.load_section', ['sectionId' => $contentInfo->sectionId])
        );
        $generator->endObjectElement('Section');

        // Main location will not exist if we're visiting the content draft
        if ($data->mainLocation !== null) {
            $generator->startObjectElement('MainLocation', 'Location');
            $generator->attribute(
                'href',
                $this->router->generate(
                    'ibexa.rest.load_location',
                    ['locationPath' => trim($mainLocation->pathString, '/')]
                )
            );
            $generator->endObjectElement('MainLocation');
        }

        $generator->startObjectElement('Locations', 'LocationList');
        $generator->attribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_locations_for_content',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Owner', 'User');
        $generator->attribute(
            'href',
            $this->router->generate('ibexa.rest.load_user', ['userId' => $contentInfo->ownerId])
        );
        $generator->endObjectElement('Owner');

        // Modification date will not exist if we're visiting the content draft
        if ($contentInfo->modificationDate !== null) {
            $generator->valueElement(
                'lastModificationDate',
                $contentInfo->modificationDate->format('c')
            );
        }

        // Published date will not exist if we're visiting the content draft
        if ($contentInfo->publishedDate !== null) {
            $generator->valueElement('publishedDate', $contentInfo->publishedDate->format('c'));
        }

        $generator->valueElement(
            'mainLanguageCode',
            $contentInfo->mainLanguageCode
        );

        $generator->valueElement(
            'currentVersionNo',
            $contentInfo->currentVersionNo
        );

        $generator->valueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $contentInfo->alwaysAvailable)
        );

        $generator->valueElement(
            'isHidden',
            $this->serializeBool($generator, $contentInfo->isHidden)
        );

        $generator->valueElement(
            'status',
            $this->getStatusString($contentInfo->status)
        );

        $generator->startObjectElement('ObjectStates', 'ContentObjectStates');
        $generator->attribute(
            'href',
            $this->router->generate(
                'ibexa.rest.get_object_states_for_content',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endObjectElement('ObjectStates');

        $generator->endObjectElement('Content');
    }

    /**
     * Maps the given content $status to a representative string.
     *
     * @param int $status
     *
     * @throws \Ibexa\Core\Base\Exceptions\BadStateException
     *
     * @return string
     */
    protected function getStatusString($status)
    {
        switch ($status) {
            case ContentInfo::STATUS_DRAFT:
                return 'DRAFT';

            case ContentInfo::STATUS_PUBLISHED:
                return 'PUBLISHED';

            case ContentInfo::STATUS_TRASHED:
                return 'TRASHED';
        }

        throw new CoreBadStateException('status', $status);
    }
}

class_alias(RestContent::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\RestContent');
