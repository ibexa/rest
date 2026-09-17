<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\MediaProcessor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\MediaProcessor::class, 'preProcessFieldSettingsHash')]
#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\MediaProcessor::class, 'postProcessFieldSettingsHash')]
class MediaProcessorTest extends BinaryInputProcessorTestCase
{
    /**
     * @var string[]
     */
    protected static array $constants = [
        'TYPE_FLASH',
        'TYPE_QUICKTIME',
        'TYPE_REALPLAYER',
        'TYPE_SILVERLIGHT',
        'TYPE_WINDOWSMEDIA',
        'TYPE_HTML5_VIDEO',
        'TYPE_HTML5_AUDIO',
    ];

    /**
     * @return array<array{array{mediaType: string}, array{mediaType: mixed}}>
     */
    public static function fieldSettingsHashes(): array
    {
        return array_map(
            static function ($constantName): array {
                return [
                    ['mediaType' => $constantName],
                    ['mediaType' => constant("Ibexa\\Core\\FieldType\\Media\\Type::{$constantName}")],
                ];
            },
            self::$constants
        );
    }

    #[DataProvider('fieldSettingsHashes')]
    public function testPreProcessFieldSettingsHash($inputSettings, $outputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->preProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @param array<string, mixed> $inputSettings
     * @param array<string, mixed> $outputSettings
     */
    #[DataProvider('fieldSettingsHashes')]
    public function testPostProcessFieldSettingsHash(array $outputSettings, array $inputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash($inputSettings)
        );
    }

    protected function getProcessor(): MediaProcessor
    {
        return new MediaProcessor($this->getTempDir());
    }
}
