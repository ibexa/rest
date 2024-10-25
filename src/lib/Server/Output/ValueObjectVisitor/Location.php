<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\Helper\RelationListHelper;
use Ibexa\Rest\Server\Values\RestContent as RestContentValue;

/**
 * Location value object visitor.
 */
class Location extends ValueObjectVisitor
{
    public function __construct(
        private readonly LocationService $locationService,
        private readonly RelationListHelper $relationListHelper
    ) {
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Location $location
     */
    public function visit(Visitor $visitor, Generator $generator, $location)
    {
        $generator->startObjectElement('Location');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Location'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('LocationUpdate'));
        $this->visitLocationAttributes($visitor, $generator, $location);
        $generator->endObjectElement('Location');
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    protected function visitLocationAttributes(
        Visitor $visitor,
        Generator $generator,
        Content\Location $location
    ) {
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_location',
                ['locationPath' => trim($location->pathString, '/')]
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $location->id);
        $generator->endValueElement('id');

        $generator->startValueElement('priority', $location->priority);
        $generator->endValueElement('priority');

        $generator->startValueElement(
            'hidden',
            $this->serializeBool($generator, $location->hidden)
        );
        $generator->endValueElement('hidden');

        $generator->startValueElement(
            'invisible',
            $this->serializeBool($generator, $location->invisible)
        );
        $generator->endValueElement('invisible');

        $generator->startObjectElement('ParentLocation', 'Location');
        if (trim($location->pathString, '/') !== '1') {
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ibexa.rest.load_location',
                    [
                        'locationPath' => implode('/', array_slice($location->path, 0, count($location->path) - 1)),
                    ]
                )
            );
            $generator->endAttribute('href');
        }
        $generator->endObjectElement('ParentLocation');

        $generator->startValueElement('pathString', $location->pathString);
        $generator->endValueElement('pathString');

        $generator->startValueElement('depth', $location->depth);
        $generator->endValueElement('depth');

        $generator->startValueElement('childCount', $this->locationService->getLocationChildCount($location));
        $generator->endValueElement('childCount');

        $generator->startValueElement('remoteId', $location->remoteId);
        $generator->endValueElement('remoteId');

        $generator->startObjectElement('Children', 'LocationList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_location_children',
                [
                    'locationPath' => trim($location->pathString, '/'),
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Children');

        $generator->startObjectElement('Content');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_content', ['contentId' => $location->contentId])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Content');

        $generator->startValueElement('sortField', $this->serializeSortField($location->sortField));
        $generator->endValueElement('sortField');

        $generator->startValueElement('sortOrder', $this->serializeSortOrder($location->sortOrder));
        $generator->endValueElement('sortOrder');

        $generator->startObjectElement('UrlAliases', 'UrlAliasRefList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.list_location_url_aliases',
                ['locationPath' => trim($location->pathString, '/')]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('UrlAliases');

        $generator->startObjectElement('ContentInfo', 'ContentInfo');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $location->contentId]
            )
        );
        $generator->endAttribute('href');

        $content = $location->getContent();
        $contentInfo = $location->getContentInfo();
        $mainLocation = $this->resolveMainLocation($contentInfo, $location);

        $visitor->visitValueObject(
            new RestContentValue(
                $contentInfo,
                $mainLocation,
                $content,
                $content->getContentType(),
                $this->relationListHelper->getRelations($content->getVersionInfo())
            )
        );

        $generator->endObjectElement('ContentInfo');
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function resolveMainLocation(
        Content\ContentInfo $contentInfo,
        Content\Location $location
    ): ?Content\Location {
        $mainLocationId = $contentInfo->getMainLocationId();
        if ($mainLocationId === null) {
            return null;
        }

        if ($mainLocationId === $location->id) {
            return $location;
        }

        try {
            return $this->locationService->loadLocation($mainLocationId);
        } catch (UnauthorizedException $e) {
            return null;
        }
    }
}
