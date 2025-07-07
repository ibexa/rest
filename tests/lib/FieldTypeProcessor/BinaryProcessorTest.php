<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\BinaryProcessor;

class BinaryProcessorTest extends BinaryInputProcessorTest
{
    public const TEMPLATE_URL = 'http://ibexa.co/subdir/var/rest_test/storage/original/{path}';

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\BinaryProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash(): void
    {
        $uri = '/var/ibexa_demo_site/storage/original/application/815b3aa9.pdf';
        $processor = $this->getProcessor();

        $inputHash = [
            'uri' => '/var/ibexa_demo_site/storage/original/application/815b3aa9.pdf',
        ];

        $outputHash = $processor->postProcessValueHash($inputHash);

        $expectedUri = 'http://static.example.com' . $uri;
        self::assertEquals(
            [
                'url' => $expectedUri,
                'uri' => $expectedUri,
            ],
            $outputHash
        );
    }

    /**
     * Returns the processor under test.
     */
    protected function getProcessor(): BinaryProcessor
    {
        return new BinaryProcessor(
            $this->getTempDir(),
            'http://static.example.com'
        );
    }
}
