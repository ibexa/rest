<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Core\FieldType\Relation\Type;
use Symfony\Component\Routing\RouterInterface;

abstract class BaseRelationProcessor extends FieldTypeProcessor
{
    private ?RouterInterface $router = null;

    private ?LocationService $locationService = null;

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setLocationService(LocationService $locationService): void
    {
        $this->locationService = $locationService;
    }

    public function canMapContentHref(): bool
    {
        return isset($this->router);
    }

    public function mapToContentHref(int $contentId): ?string
    {
        return $this->router?->generate('ibexa.rest.load_content', ['contentId' => $contentId]) ?? '';
    }

    public function mapToLocationHref(int $locationId): ?string
    {
        try {
            $location = $this->locationService?->loadLocation($locationId);
        } catch (UnauthorizedException | NotFoundException $e) {
            return '';
        }

        if ($location === null) {
            return '';
        }

        return $this->router?->generate('ibexa.rest.load_location', [
            'locationPath' => $location->getPathString(),
        ]) ?? '';
    }

    public function preProcessFieldSettingsHash(mixed $incomingSettingsHash): mixed
    {
        if (isset($incomingSettingsHash['selectionMethod'])) {
            switch ($incomingSettingsHash['selectionMethod']) {
                case 'SELECTION_BROWSE':
                    $incomingSettingsHash['selectionMethod'] = Type::SELECTION_BROWSE;
                    break;
                case 'SELECTION_DROPDOWN':
                    $incomingSettingsHash['selectionMethod'] = Type::SELECTION_DROPDOWN;
            }
        }

        return $incomingSettingsHash;
    }

    public function postProcessFieldSettingsHash(mixed $outgoingSettingsHash): mixed
    {
        if (isset($outgoingSettingsHash['selectionMethod'])) {
            switch ($outgoingSettingsHash['selectionMethod']) {
                case Type::SELECTION_BROWSE:
                    $outgoingSettingsHash['selectionMethod'] = 'SELECTION_BROWSE';
                    break;
                case Type::SELECTION_DROPDOWN:
                    $outgoingSettingsHash['selectionMethod'] = 'SELECTION_DROPDOWN';
            }
        }

        return $outgoingSettingsHash;
    }
}
