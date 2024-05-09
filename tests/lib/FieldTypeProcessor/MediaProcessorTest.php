<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\MediaProcessor;

class MediaProcessorTest extends BinaryInputProcessorTest
{
    protected $constants = [
        'TYPE_FLASH',
        'TYPE_QUICKTIME',
        'TYPE_REALPLAYER',
        'TYPE_SILVERLIGHT',
        'TYPE_WINDOWSMEDIA',
        'TYPE_HTML5_VIDEO',
        'TYPE_HTML5_AUDIO',
    ];

    public function fieldSettingsHashes()
    {
        return array_map(
            static function ($constantName) {
                return [
                    ['mediaType' => $constantName],
                    ['mediaType' => constant("Ibexa\\Core\\FieldType\\Media\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\MediaProcessor::preProcessFieldSettingsHash
     *
     * @dataProvider fieldSettingsHashes
     */
    public function testPreProcessFieldSettingsHash($inputSettings, $outputSettings)
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->preProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\MediaProcessor::postProcessFieldSettingsHash
     *
     * @dataProvider fieldSettingsHashes
     */
    public function testPostProcessFieldSettingsHash($outputSettings, $inputSettings)
    {
        $processor = $this->getProcessor();

        self::assertEquals(
            $outputSettings,
            $processor->postProcessFieldSettingsHash($inputSettings)
        );
    }

    /**
     * @return \Ibexa\Rest\FieldTypeProcessor\MediaProcessor
     */
    protected function getProcessor()
    {
        return new MediaProcessor($this->getTempDir());
    }
}

class_alias(MediaProcessorTest::class, 'EzSystems\EzPlatformRest\Tests\FieldTypeProcessor\MediaProcessorTest');
