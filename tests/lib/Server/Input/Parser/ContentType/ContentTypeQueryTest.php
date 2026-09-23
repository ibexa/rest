<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Server\Input\Parser\ContentType;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\ContentTypeQuery as ContentTypeQueryValueObject;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContainsFieldDefinitionId;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeGroupId;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeId;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\ContentTypeIdentifier;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\IsSystem;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\Criterion\LogicalAnd;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\CriterionInterface;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\SortClause\Identifier;
use Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\CriterionProcessor;
use Ibexa\Rest\Server\Input\Parser\ContentType\Query\ContentTypeQuery;
use Ibexa\Rest\Server\Input\Parser\ContentType\SortClause\SortClauseProcessor;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTestCase;

final class ContentTypeQueryTest extends BaseTestCase
{
    public function testParse(): void
    {
        $data = [
            'limit' => 1,
            'offset' => 0,
            'Query' => [
                'ContentTypeIdCriterion' => [1, 2],
                'ContentTypeIdentifierCriterion' => 'folder',
                'IsSystemCriterion' => true,
                'ContentTypeGroupIdCriterion' => 1,
                'ContainsFieldDefinitionIdCriterion' => 2,
            ],
            'SortClauses' => [
                'Identifier' => 'descending',
            ],
        ];

        $parsingDispatcherMock = $this->getParsingDispatcherMock();

        $matcher = self::exactly(6);
        $parsingDispatcherMock
            ->expects($matcher)
            ->method('parse')
            ->willReturnCallback(static function (...$parameters) use ($matcher): CriterionInterface|SortClause {
                return match ($matcher->numberOfInvocations()) {
                    1 => new ContentTypeId([1, 2]),
                    2 => new ContentTypeIdentifier('folder'),
                    3 => new IsSystem(true),
                    4 => new ContentTypeGroupId(1),
                    5 => new ContainsFieldDefinitionId(1),
                    6 => new Identifier(SortClause::SORT_DESC),
                    default => self::fail('Unexpected call to parse().'),
                };
            });

        $result = $this->getParser()->parse($data, $this->getParsingDispatcherMock());

        self::assertInstanceOf(ContentTypeQueryValueObject::class, $result);
        self::assertSame(1, $result->getLimit());
        self::assertSame(0, $result->getOffset());
        self::assertInstanceOf(Identifier::class, $result->getSortClauses()[0]);

        $criterion = $result->getCriterion();
        self::assertInstanceOf(LogicalAnd::class, $criterion);
        self::assertCount(5, $criterion->getCriteria());
    }

    protected function internalGetParser(): ContentTypeQuery
    {
        $criterionProcessor = new CriterionProcessor($this->getParsingDispatcherMock());
        $sortClause = new SortClauseProcessor($this->getParsingDispatcherMock());

        return new ContentTypeQuery(
            $criterionProcessor,
            $sortClause,
        );
    }
}
