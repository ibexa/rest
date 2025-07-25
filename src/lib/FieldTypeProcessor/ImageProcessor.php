<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

use Symfony\Component\Routing\RouterInterface;

class ImageProcessor extends BinaryInputProcessor
{
    /**
     * Template for image URLs.
     */
    protected string $urlTemplate;

    /**
     * Array of variations identifiers.
     *
     * <code>
     * array( 'small', 'thumbnail', 'large' )
     * </code>
     *
     * @var string[]
     */
    protected array $variations;

    protected RouterInterface $router;

    /**
     * @param array $variations array of variations identifiers
     */
    public function __construct(string $temporaryDirectory, RouterInterface $router, array $variations)
    {
        parent::__construct($temporaryDirectory);
        $this->router = $router;
        $this->variations = $variations;
    }

    public function preProcessValueHash(mixed $incomingValueHash): mixed
    {
        if (is_array($incomingValueHash) && array_key_exists('variations', $incomingValueHash)) {
            unset($incomingValueHash['variations']);
        }

        return parent::preProcessValueHash($incomingValueHash);
    }

    /**
     * {@inheritdoc}
     */
    public function postProcessValueHash(mixed $outgoingValueHash): mixed
    {
        if (!is_array($outgoingValueHash)) {
            return $outgoingValueHash;
        }

        $outgoingValueHash['path'] = '/' . $outgoingValueHash['inputUri'];
        foreach ($this->variations as $variationIdentifier) {
            $outgoingValueHash['variations'][$variationIdentifier] = [
                'href' => $this->router->generate(
                    'ibexa.rest.binary_content.get_image_variation',
                    [
                        'imageId' => $outgoingValueHash['imageId'],
                        'variationIdentifier' => $variationIdentifier,
                    ]
                ),
            ];
        }

        return $outgoingValueHash;
    }

    /**
     * Generates a URL for $path in $variation.
     */
    protected function generateUrl(string $path, string $variation): string
    {
        $fieldId = '';
        $versionNo = '';

        // 223-1-eng-US/Cool-File.jpg
        if (preg_match('((?<id>[0-9]+)-(?<version>[0-9]+)-[^/]+/[^/]+$)', $path, $matches)) {
            $fieldId = $matches['id'];
            $versionNo = $matches['version'];
        }

        return str_replace(
            [
                '{variation}',
                '{fieldId}',
                '{versionNo}',
            ],
            [
                $variation,
                $fieldId,
                $versionNo,
            ],
            $this->urlTemplate
        );
    }
}
