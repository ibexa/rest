<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use PHPUnit\Framework\TestCase;

abstract class BinaryInputProcessorTest extends TestCase
{
    private $tempDir;

    public function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->getTempDir(),
                \FileSystemIterator::KEY_AS_PATHNAME | \FileSystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO
            ),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        parent::tearDown();
    }

    /**
     * Returns a temp directory path and creates it, if necessary.
     *
     * @return string The directory path
     */
    protected function getTempDir()
    {
        if (!isset($this->tempDir)) {
            $tempFile = tempnam(
                sys_get_temp_dir(),
                'eZ_REST_BinaryInput'
            );

            unlink($tempFile);

            $this->tempDir = $tempFile;

            mkdir($this->tempDir);
        }

        return $this->tempDir;
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\BinaryInputProcessor::preProcessValueHash
     */
    public function testPreProcessValueHashMissingKey()
    {
        $processor = $this->getProcessor();

        $inputHash = ['foo' => 'bar'];

        $outputHash = $processor->preProcessValueHash($inputHash);

        $this->assertEquals($inputHash, $outputHash);
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\BinaryInputProcessor::preProcessValueHash
     */
    public function testPreProcessValueHash()
    {
        $processor = $this->getProcessor();

        $fileContent = '42';

        $inputHash = ['data' => base64_encode($fileContent)];

        $outputHash = $processor->preProcessValueHash($inputHash);

        $this->assertFalse(isset($outputHash['data']), 'Data found in input hash');
        $this->assertTrue(isset($outputHash['inputUri']), 'No path found in output hash');

        $this->assertFileExists($outputHash['inputUri'], "The output path {$outputHash['inputUri']} does not exist");

        $this->assertEquals($fileContent, file_get_contents($outputHash['inputUri']));
    }

    /**
     * Returns the processor under test.
     *
     * @return \Ibexa\Rest\FieldTypeProcessor\BinaryInputProcessor
     */
    abstract protected function getProcessor();
}

class_alias(BinaryInputProcessorTest::class, 'EzSystems\EzPlatformRest\Tests\FieldTypeProcessor\BinaryInputProcessorTest');
