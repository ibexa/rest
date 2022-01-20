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

        $generator->startAttribute(
            'href',
            $data->path === null ?
                $this->router->generate('ibexa.rest.load_content', ['contentId' => $contentInfo->id]) :
                $data->path
        );
        $generator->endAttribute('href');

        $generator->startAttribute('remoteId', $contentInfo->remoteId);
        $generator->endAttribute('remoteId');
        $generator->startAttribute('id', $contentInfo->id);
        $generator->endAttribute('id');

        $generator->startObjectElement('ContentType');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content_type',
                ['contentTypeId' => $contentInfo->contentTypeId]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ContentType');

        $generator->startValueElement('Name', $contentInfo->name);
        $generator->endValueElement('Name');

        $generator->startValueElement('TranslatedName', $translatedContentName);
        $generator->endValueElement('TranslatedName');

        $generator->startObjectElement('Versions', 'VersionList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_content_versions', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('CurrentVersion', 'Version');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.redirect_current_version',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endAttribute('href');

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
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_section', ['sectionId' => $contentInfo->sectionId])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Section');

        // Main location will not exist if we're visiting the content draft
        if ($data->mainLocation !== null) {
            $generator->startObjectElement('MainLocation', 'Location');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ibexa.rest.load_location',
                    ['locationPath' => trim($mainLocation->pathString, '/')]
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('MainLocation');
        }

        $generator->startObjectElement('Locations', 'LocationList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_locations_for_content',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Owner', 'User');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_user', ['userId' => $contentInfo->ownerId])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Owner');

        // Modification date will not exist if we're visiting the content draft
        if ($contentInfo->modificationDate !== null) {
            $generator->startValueElement(
                'lastModificationDate',
                $contentInfo->modificationDate->format('c')
            );
            $generator->endValueElement('lastModificationDate');
        }

        // Published date will not exist if we're visiting the content draft
        if ($contentInfo->publishedDate !== null) {
            $generator->startValueElement(
                'publishedDate',
                ($contentInfo->publishedDate !== null
                    ? $contentInfo->publishedDate->format('c')
                    : null)
            );
            $generator->endValueElement('publishedDate');
        }

        $generator->startValueElement(
            'mainLanguageCode',
            $contentInfo->mainLanguageCode
        );
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement(
            'currentVersionNo',
            $contentInfo->currentVersionNo
        );
        $generator->endValueElement('currentVersionNo');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $contentInfo->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $generator->startValueElement(
            'isHidden',
            $this->serializeBool($generator, $contentInfo->isHidden)
        );
        $generator->endValueElement('isHidden');

        $generator->startValueElement(
            'status',
            $this->getStatusString($contentInfo->status)
        );
        $generator->endValueElement('status');

        $generator->startObjectElement('ObjectStates', 'ContentObjectStates');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.get_object_states_for_content',
                ['contentId' => $contentInfo->id]
            )
        );
        $generator->endAttribute('href');
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
