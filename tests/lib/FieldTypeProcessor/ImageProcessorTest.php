<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\ImageProcessor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RouterInterface;

#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\ImageProcessor::class, 'postProcessValueHash')]
class ImageProcessorTest extends BinaryInputProcessorTestCase
{
    protected RouterInterface&MockObject $router;

    public function testPostProcessValueHash(): void
    {
        $processor = $this->getProcessor();

        $inputHash = [
            'inputUri' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
            'imageId' => '223-12345',
        ];

        $variations = $this->getVariations();
        $expectedVariations = [];
        foreach ($variations as $variationIdentifier) {
            $expectedVariations[$variationIdentifier]['href'] = "/content/binary/images/{$inputHash['imageId']}/variations/{$variationIdentifier}";
        }

        $routerMock = $this->getRouterMock();
        $matcher = self::exactly(count($variations));
        $routerMock
            ->expects($matcher)
            ->method('generate')
            ->willReturnCallback(static function (string $route, array $parameters) use ($matcher, $variations, $inputHash, $expectedVariations) {
                $variationIdentifier = $variations[$matcher->numberOfInvocations() - 1];

                self::assertSame('ibexa.rest.binary_content.get_image_variation', $route);
                self::assertSame(
                    ['imageId' => $inputHash['imageId'], 'variationIdentifier' => $variationIdentifier],
                    $parameters
                );

                return $expectedVariations[$variationIdentifier]['href'];
            });

        $outputHash = $processor->postProcessValueHash($inputHash);

        self::assertEquals(
            [
                'inputUri' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
                'path' => '/var/some_site/223-1-eng-US/Cool-File.jpg',
                'imageId' => '223-12345',
                'variations' => $expectedVariations,
            ],
            $outputHash
        );
    }

    protected function getProcessor(): ImageProcessor
    {
        return new ImageProcessor(
            $this->getTempDir(),
            $this->getRouterMock(),
            $this->getVariations()
        );
    }

    protected function getRouterMock(): RouterInterface&MockObject
    {
        if (!isset($this->router)) {
            $this->router = $this->createMock(RouterInterface::class);
        }

        return $this->router;
    }

    /**
     * @return array<string>
     */
    protected function getVariations(): array
    {
        return ['small', 'medium', 'large'];
    }
}
