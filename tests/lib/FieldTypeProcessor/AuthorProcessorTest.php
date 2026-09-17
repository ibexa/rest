<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Ibexa\Rest\FieldTypeProcessor\AuthorProcessor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\AuthorProcessor::class, 'preProcessFieldSettingsHash')]
#[CoversMethod(\Ibexa\Rest\FieldTypeProcessor\AuthorProcessor::class, 'postProcessFieldSettingsHash')]
class AuthorProcessorTest extends TestCase
{
    /**
     * @var string[]
     */
    protected static array $constants = [
        'DEFAULT_VALUE_EMPTY',
        'DEFAULT_CURRENT_USER',
    ];

    /**
     * @return array<array{array{defaultAuthor: mixed}, array{defaultAuthor: mixed}}>
     */
    public static function fieldSettingsHashes(): array
    {
        return array_map(
            static function ($constantName): array {
                return [
                    ['defaultAuthor' => $constantName],
                    ['defaultAuthor' => constant("Ibexa\\Core\\FieldType\\Author\\Type::{$constantName}")],
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

    protected function getProcessor(): AuthorProcessor
    {
        return new AuthorProcessor();
    }
}
