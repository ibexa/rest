<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\ImageProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RouterInterface;

class ImageProcessorTest extends BinaryInputProcessorTest
{
    /** @var (RouterInterface&MockObject)|null */
    protected $routerMock;

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\ImageProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $inputHash = [
            'path' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
            'imageId' => '223-12345',
        ];

        $routerMock = $this->getRouterMock();
        $variations = $this->getVariations();
        $expectedVariations = [];
        foreach ($variations as $variationIdentifier) {
            $expectedVariations[$variationIdentifier]['href'] = "/content/binary/images/{$inputHash['imageId']}/variations/{$variationIdentifier}";
        }

        $matcher = $this->exactly(count($variations));
        $routerMock
            ->expects($matcher)
            ->method('generate')
            ->willReturnCallback(static function (
                string $route,
                array $parameters
            ) use ($matcher, $variations, $inputHash, $expectedVariations): string {
                $variationIdentifier = $variations[$matcher->getInvocationCount() - 1];

                self::assertSame('ibexa.rest.binary_content.get_image_variation', $route);
                self::assertSame(
                    ['imageId' => $inputHash['imageId'], 'variationIdentifier' => $variationIdentifier],
                    $parameters
                );

                return $expectedVariations[$variationIdentifier]['href'];
            });

        $outputHash = $processor->postProcessValueHash($inputHash);

        $this->assertEquals(
            [
                'path' => '/var/some_site/223-1-eng-US/Cool-File.jpg',
                'imageId' => '223-12345',
                'variations' => $expectedVariations,
            ],
            $outputHash
        );
    }

    /**
     * Returns the processor under test.
     *
     * @return ImageProcessor
     */
    protected function getProcessor()
    {
        return new ImageProcessor(
            $this->getTempDir(),
            $this->getRouterMock(),
            $this->getVariations()
        );
    }

    /**
     * @return RouterInterface&MockObject
     */
    protected function getRouterMock()
    {
        if (!isset($this->routerMock)) {
            $this->routerMock = $this->createMock(RouterInterface::class);
        }

        return $this->routerMock;
    }

    protected function getVariations()
    {
        return ['small', 'medium', 'large'];
    }
}

class_alias(ImageProcessorTest::class, 'EzSystems\EzPlatformRest\Tests\FieldTypeProcessor\ImageProcessorTest');
