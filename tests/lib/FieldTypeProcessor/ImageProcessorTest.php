<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\ImageProcessor;
use Ibexa\Rest\RequestParser;
use Symfony\Component\Routing\RouterInterface;

class ImageProcessorTest extends BinaryInputProcessorTest
{
    /** @var RequestParser */
    protected $requestParser;

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
        $expectedArguments = [];
        $hrefs = [];
        foreach ($this->getVariations() as $variationIdentifier) {
            $expectedVariations[$variationIdentifier]['href'] = "/content/binary/images/{$inputHash['imageId']}/variations/{$variationIdentifier}";
            $expectedArguments[] = [
                'ibexa.rest.binary_content.get_image_variation',
                ['imageId' => $inputHash['imageId'], 'variationIdentifier' => $variationIdentifier],
            ];
            $hrefs[] = $expectedVariations[$variationIdentifier]['href'];
        }
        $routerMock
            ->expects($this->exactly(count($expectedArguments)))
            ->method('generate')
            ->withConsecutive(...$expectedArguments)
            ->willReturnOnConsecutiveCalls(...$hrefs);

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
     * @returns \Symfony\Component\Routing\RouterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRouterMock()
    {
        if (!isset($this->requestParser)) {
            $this->requestParser = $this->createMock(RouterInterface::class);
        }

        return $this->requestParser;
    }

    protected function getVariations()
    {
        return ['small', 'medium', 'large'];
    }
}

class_alias(ImageProcessorTest::class, 'EzSystems\EzPlatformRest\Tests\FieldTypeProcessor\ImageProcessorTest');
