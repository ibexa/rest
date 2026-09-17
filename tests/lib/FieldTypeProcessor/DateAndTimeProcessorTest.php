<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\DateAndTimeProcessor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\DateAndTimeProcessor::class, 'preProcessFieldSettingsHash')]
#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\DateAndTimeProcessor::class, 'postProcessFieldSettingsHash')]
class DateAndTimeProcessorTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected static array $constants = [
        'DEFAULT_EMPTY',
        'DEFAULT_CURRENT_DATE',
        'DEFAULT_CURRENT_DATE_ADJUSTED',
    ];

    /**
     * @return array<array{array{defaultType: mixed}, array{defaultType: mixed}}>
     */
    public static function fieldSettingsHashes(): array
    {
        return array_map(
            static function ($constantName): array {
                return [
                    ['defaultType' => $constantName],
                    ['defaultType' => constant("Ibexa\\Core\\FieldType\\DateAndTime\\Type::{$constantName}")],
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

    #[DataProvider('fieldSettingsHashes')]
    public function testPostProcessFieldSettingsHash($outputSettings, $inputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash($inputSettings)
        );
    }

    protected function getProcessor(): DateAndTimeProcessor
    {
        return new DateAndTimeProcessor();
    }
}
