<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\UserMetadata as UserMetadataCriterion;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\Criterion\UserMetadata;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTest;

class UserMetadataTest extends BaseTest
{
    /**
     * @return array<array{0: array{UserMetadataCriterion: array{Target: string, Value: string|int|array<int>}}, 1: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\UserMetadata}>
     */
    public function testParseProvider(): array
    {
        return [
            [
                ['UserMetadataCriterion' => ['Target' => 'owner', 'Value' => 14]],
                new UserMetadataCriterion('owner', Operator::IN, [14]),
            ],
            [
                ['UserMetadataCriterion' => ['Target' => 'owner', 'Value' => '14,15,42']],
                new UserMetadataCriterion('owner', Operator::IN, [14, 15, 42]),
            ],
            [
                ['UserMetadataCriterion' => ['Target' => 'owner', 'Value' => [14, 15, 42]]],
                new UserMetadataCriterion('owner', Operator::IN, [14, 15, 42]),
            ],
        ];
    }

    /**
     * @param array<array{0: array{UserMetadataCriterion: array{Target: string, Value: string|int|array<int>}}, 1: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\UserMetadata}> $data
     *
     * @dataProvider testParseProvider
     */
    public function testParse(array $data, UserMetadataCriterion $expected): void
    {
        $userMetadata = $this->getParser();
        $result = $userMetadata->parse($data, $this->getParsingDispatcherMock());

        self::assertEquals(
            $expected,
            $result,
            'UserMetadata parser not created correctly.'
        );
    }

    public function testParseExceptionOnInvalidCriterionFormat(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid <UserMetadataCriterion> format');
        $inputArray = [
            'foo' => 'Michael learns to mock',
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidTargetFormat(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid <Target> format');
        $inputArray = [
            'UserMetadataCriterion' => [
                'foo' => 'Mock around the clock',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidValueFormat(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid <Value> format');
        $inputArray = [
            'UserMetadataCriterion' => [
                'Target' => 'Moxette',
                'foo' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnWrongValueType(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid <Value> format');
        $inputArray = [
            'UserMetadataCriterion' => [
                'Target' => 'We will mock you',
                'Value' => new \stdClass(),
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): UserMetadata
    {
        return new UserMetadata();
    }
}
