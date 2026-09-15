<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\TimeProcessor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\TimeProcessor::class, 'preProcessFieldSettingsHash')]
#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\TimeProcessor::class, 'postProcessFieldSettingsHash')]
class TimeProcessorTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected static array $constants = [
        'DEFAULT_EMPTY',
        'DEFAULT_CURRENT_TIME',
    ];

    /**
     * @return array<array{array{defaultType: mixed}, array{defaultType: mixed}}>
     */
    public static function fieldSettingsHashes(): array
    {
        return array_map(
            static function (string $constantName): array {
                return [
                    ['defaultType' => $constantName],
                    ['defaultType' => constant("Ibexa\\Core\\FieldType\\Time\\Type::{$constantName}")],
                ];
            },
            self::$constants
        );
    }

    /**
     * @param array<string, mixed> $inputSettings
     * @param array<string, mixed> $outputSettings
     */
    #[DataProvider('fieldSettingsHashes')]
    public function testPreProcessFieldSettingsHash(array $inputSettings, array $outputSettings): void
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

    protected function getProcessor(): TimeProcessor
    {
        return new TimeProcessor();
    }
}
