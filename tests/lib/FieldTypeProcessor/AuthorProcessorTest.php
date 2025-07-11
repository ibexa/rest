<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\AuthorProcessor;
use PHPUnit\Framework\TestCase;

class AuthorProcessorTest extends TestCase
{
    /**
     * @var string[]
     */
    protected array $constants = [
        'DEFAULT_VALUE_EMPTY',
        'DEFAULT_CURRENT_USER',
    ];

    /**
     * @return array<array{array{defaultAuthor: mixed}, array{defaultAuthor: mixed}}>
     */
    public function fieldSettingsHashes(): array
    {
        return array_map(
            static function ($constantName): array {
                return [
                    ['defaultAuthor' => $constantName],
                    ['defaultAuthor' => constant("Ibexa\\Core\\FieldType\\Author\\Type::{$constantName}")],
                ];
            },
            $this->constants
        );
    }

    /**
     * @covers \Ibexa\Rest\FieldTypeProcessor\AuthorProcessor::preProcessFieldSettingsHash
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
     * @covers \Ibexa\Rest\FieldTypeProcessor\AuthorProcessor::postProcessFieldSettingsHash
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

    protected function getProcessor(): AuthorProcessor
    {
        return new AuthorProcessor();
    }
}
