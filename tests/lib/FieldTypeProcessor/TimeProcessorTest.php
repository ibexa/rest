<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\TimeProcessor;
use PHPUnit\Framework\TestCase;

class TimeProcessorTest extends TestCase
{
    protected $constants = [
        'DEFAULT_EMPTY',
        'DEFAULT_CURRENT_TIME',
    ];

    public function fieldSettingsHashes(): array
    {
        return array_map(
            static function ($constantName): array {
                return [
                    ['defaultType' => $constantName],
                    ['defaultType' => constant("Ibexa\\Core\\FieldType\\Time\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\TimeProcessor::preProcessFieldSettingsHash
     *
     * @dataProvider fieldSettingsHashes
     */
    public function testPreProcessFieldSettingsHash($inputSettings, $outputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->preProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\TimeProcessor::postProcessFieldSettingsHash
     *
     * @dataProvider fieldSettingsHashes
     */
    public function testPostProcessFieldSettingsHash($outputSettings, $inputSettings): void
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @return \Ibexa\Rest\FieldTypeProcessor\TimeProcessor
     */
    protected function getProcessor(): TimeProcessor
    {
        return new TimeProcessor();
    }
}
