<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content;
use Ibexa\Contracts\Rest\Exceptions\Parser as ParserException;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser;
use Ibexa\Rest\Server\Input\Parser\Criterion\LogicalAnd;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTest;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LogicalAndTest extends BaseTest
{
    /**
     * Logical parsing of AND statement.
     *
     * Notice regarding multiple criteria of same type:
     *
     * The XML decoder of EZ is not creating numeric arrays, instead using the tag as the array key. See
     * variable $logicalAndParsedFromXml. This causes the ContentTypeIdentifierCriterion-Tag to appear as one-element
     * (type numeric array) and two criteria configuration inside. The logical or parser will take care
     * of this and return a flatt LogicalAnd criterion with 3 criteria inside.
     *
     * ```
     * <AND>
     *   <ContentTypeIdentifierCriterion>author</ContentTypeIdentifierCriterion>
     *   <ContentTypeIdentifierCriterion>book</ContentTypeIdentifierCriterion>
     *   <Field>
     *     <name>title</name>
     *     <operator>EQ</operator>
     *     <value>Contributing to projects</value>
     *   </Field>
     * </AND>
     * ```
     */
    public function testParseLogicalAnd(): void
    {
        $logicalAndParsedFromXml = [
            'AND' => [
                'ContentTypeIdentifierCriterion' => [
                    0 => 'author',
                    1 => 'book',
                ],
                'Field' => [
                    'name' => 'title',
                    'operator' => 'EQ',
                    'value' => 'Contributing to projects',
                ],
            ],
        ];

        $criterionMock = $this->createMock(Content\Query\Criterion::class);

        $parserMock = $this->createMock(\Ibexa\Contracts\Rest\Input\Parser::class);
        $parserMock->method('parse')->willReturn($criterionMock);

        $result = $this->internalGetParser()->parse($logicalAndParsedFromXml, new ParsingDispatcher(
            $this->createMock(EventDispatcherInterface::class),
            [
                'application/vnd.ibexa.api.internal.criterion.ContentTypeIdentifier' => $parserMock,
                'application/vnd.ibexa.api.internal.criterion.Field' => $parserMock,
            ]
        ));

        self::assertCount(3, $result->criteria);
    }

    public function testThrowsExceptionOnInvalidAndStatement(): void
    {
        $this->expectException(ParserException::class);
        $this->internalGetParser()->parse(['AND' => 'Should be an array'], new ParsingDispatcher(
            $this->createMock(EventDispatcherInterface::class)
        ));
    }

    protected function internalGetParser(): LogicalAnd
    {
        return new LogicalAnd();
    }
}
