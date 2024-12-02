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
    protected RouterInterface&MockObject $router;

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\ImageProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $inputHash = [
            'inputUri' => 'var/some_site/223-1-eng-US/Cool-File.jpg',
            'imageId' => '223-12345',
        ];

        $routerMock = $this->getRouterMock();
        foreach ($this->getVariations() as $iteration => $variationIdentifier) {
            $expectedVariations[$variationIdentifier]['href'] = "/content/binary/images/{$inputHash['imageId']}/variations/{$variationIdentifier}";
            $routerMock
                ->expects(self::at($iteration))
                ->method('generate')
                ->with(
                    'ibexa.rest.binary_content.get_image_variation',
                    ['imageId' => $inputHash['imageId'], 'variationIdentifier' => $variationIdentifier]
                )
                ->willReturn(
                    $expectedVariations[$variationIdentifier]['href']
                );
        }

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

    /**
     * Returns the processor under test.
     *
     * @return \Ibexa\Rest\FieldTypeProcessor\ImageProcessor
     */
    protected function getProcessor()
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

    protected function getVariations()
    {
        return ['small', 'medium', 'large'];
    }
}
