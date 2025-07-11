<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Symfony\Component\Routing\RouterInterface;

class ImageAssetFieldTypeProcessor extends FieldTypeProcessor
{
    private RouterInterface $router;

    private ContentService $contentService;

    /** @var string[] */
    private array $configMappings;

    /** @var string[] */
    private array $variations;

    public function __construct(
        RouterInterface $router,
        ContentService $contentService,
        array $configMappings,
        array $variations
    ) {
        $this->router = $router;
        $this->variations = $variations;
        $this->contentService = $contentService;
        $this->configMappings = $configMappings;
    }

    public function postProcessValueHash(mixed $outgoingValueHash): mixed
    {
        if (!\is_array($outgoingValueHash)) {
            return $outgoingValueHash;
        }

        if ($outgoingValueHash['destinationContentId'] === null) {
            return $outgoingValueHash;
        }

        try {
            $imageContent = $this->contentService->loadContent((int) $outgoingValueHash['destinationContentId']);
        } catch (NotFoundException $e) {
            return $outgoingValueHash;
        }

        $field = $imageContent->getField($this->configMappings['content_field_identifier']);

        $imageId = $imageContent->id . '-' . $field->id . '-' . $imageContent->versionInfo->versionNo;
        foreach ($this->variations as $variationIdentifier) {
            $outgoingValueHash['variations'][$variationIdentifier] = [
                'href' => $this->router->generate(
                    'ibexa.rest.binary_content.get_image_variation',
                    [
                        'imageId' => $imageId,
                        'variationIdentifier' => $variationIdentifier,
                    ]
                ),
            ];
        }

        return $outgoingValueHash;
    }
}
