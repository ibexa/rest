<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\SortClause;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\DatePublished;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\SortClause\DataKeyValueObjectClass;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTest;

class DataKeyValueObjectClassTest extends BaseTest
{
    /**
     * Tests the DataKeyValueObjectClass parser.
     */
    public function testParse(): void
    {
        $inputArray = [
            'DatePublished' => Query::SORT_ASC,
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $result = $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertEquals(
            new DatePublished(Query::SORT_ASC),
            $result,
            'DataKeyValueObjectClass parser not created correctly.'
        );
    }

    /**
     * Test DataKeyValueObjectClass parser throwing exception on missing sort clause.
     */
    public function testParseExceptionOnMissingSortClause(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('The <DatePublished> Sort Clause doesn\'t exist in the input structure');
        $inputArray = [
            'name' => 'Keep on mocking in the free world',
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DataKeyValueObjectClass parser throwing exception on invalid direction format.
     */
    public function testParseExceptionOnInvalidDirectionFormat(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid direction format in the <DatePublished> Sort Clause');
        $inputArray = [
            'DatePublished' => 'Jailhouse Mock',
        ];

        $dataKeyValueObjectClass = $this->getParser();
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test DataKeyValueObjectClass parser throwing exception on nonexisting value object class.
     */
    public function testParseExceptionOnNonexistingValueObjectClass(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Value object class <eC\Pubish\APl\Repudiatory\BadValues\Discontent\Queezy\SantaClause\ThisClassIsExistentiallyChallenged> is not defined');
        $inputArray = [
            'DatePublished' => Query::SORT_ASC,
        ];

        $dataKeyValueObjectClass = new DataKeyValueObjectClass(
            'DatePublished',
            'eC\Pubish\APl\Repudiatory\BadValues\Discontent\Queezy\SantaClause\ThisClassIsExistentiallyChallenged'
        );
        $dataKeyValueObjectClass->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the DataKeyValueObjectClass parser.
     */
    protected function internalGetParser(): DataKeyValueObjectClass
    {
        return new DataKeyValueObjectClass(
            'DatePublished',
            'Ibexa\\Contracts\\Core\\Repository\\Values\\Content\\Query\\SortClause\\DatePublished'
        );
    }
}
