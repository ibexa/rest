<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata as DateMetadataCriterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Rest\Server\Input\Parser\Criterion\DateMetadata;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTest;

final class DateMetadataTest extends BaseTest
{
    public function testParseProvider(): iterable
    {
        return [
            [
                ['DateMetadataCriterion' => ['Target' => 'modified', 'Value' => [14, 1620739489], 'Operator' => 'BETWEEN']],
                new DateMetadataCriterion('modified', Operator::BETWEEN, [14, 1620739489]),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'modified', 'Value' => 14, 'Operator' => 'GT']],
                new DateMetadataCriterion('modified', Operator::GT, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 14, 'Operator' => 'GTE']],
                new DateMetadataCriterion('created', Operator::GTE, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 14, 'Operator' => 'EQ']],
                new DateMetadataCriterion('created', Operator::EQ, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => '14', 'Operator' => 'EQ']],
                new DateMetadataCriterion('created', Operator::EQ, 14),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 1620739489, 'Operator' => 'LT']],
                new DateMetadataCriterion('created', Operator::LT, 1620739489),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => 1620739489, 'Operator' => 'LTE']],
                new DateMetadataCriterion('created', Operator::LTE, 1620739489),
            ],
            [
                ['DateMetadataCriterion' => ['Target' => 'created', 'Value' => [14, 58, 167, 165245, 1620739489], 'Operator' => 'IN']],
                new DateMetadataCriterion('created', Operator::IN, [14, 58, 167, 165245, 1620739489]),
            ],
        ];
    }

    /**
     * Tests the DateMetaData parser.
     *
     * @param string[] $data
     *
     * @dataProvider testParseProvider
     */
    public function testParse(array $data, DateMetadataCriterion $expected): void
    {
        $dateMetadata = $this->getParser();
        $result = $dateMetadata->parse($data, $this->getParsingDispatcherMock());

        self::assertEquals(
            $expected,
            $result,
            'DateMetadata parser not created correctly.'
        );
    }

    public function testParseExceptionOnInvalidCriterionFormat(): void
    {
        $this->expectExceptionMessage('Invalid <DateMetadataCriterion> format');
        $this->expectException(Exceptions\Parser::class);
        $inputArray = [
            'foo' => 'Michael learns to mock',
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidTargetFormat(): void
    {
        $this->expectExceptionMessage('Invalid <Target> format');
        $this->expectException(Exceptions\Parser::class);

        $inputArray = [
            'DateMetadataCriterion' => [
                'foo' => 'Mock around the clock',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnWrongTargetType(): void
    {
        $this->expectExceptionMessage('Invalid <Target> format');
        $this->expectException(Exceptions\Parser::class);

        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'Mock around the clock',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidValueFormat(): void
    {
        $this->expectExceptionMessage('Invalid <Value> format');
        $this->expectException(Exceptions\Parser::class);

        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'foo' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnWrongValueType(): void
    {
        $this->expectExceptionMessage('Invalid <Value> format');
        $this->expectException(Exceptions\Parser::class);

        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'Value' => new \stdClass(),
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidOperatorFormat(): void
    {
        $this->expectExceptionMessage('Invalid <Operator> format');
        $this->expectException(Exceptions\Parser::class);

        $inputArray = [
            'DateMetadataCriterion' => [
                'Target' => 'modified',
                'Value' => 42,
            ],
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): DateMetadata
    {
        return new DateMetadata();
    }
}
