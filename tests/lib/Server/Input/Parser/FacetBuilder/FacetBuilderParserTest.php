<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\FacetBuilder;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\CriterionFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\FieldFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\LocationFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\SectionFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\TermFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Rest\Server\Input\Parser\Criterion\LocationId;

class FacetBuilderParserTest extends FacetBuilderBaseTest
{
    public function testGenericFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'ContentType' => [
                    'name' => 'Generic test',
                    'limit' => 5,
                    'minCount' => 3,
                    'global' => true,
                    'filter' => new LocationId(123),
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new ContentTypeFacetBuilder([
                    'name' => 'Generic test',
                    'limit' => 5,
                    'minCount' => 3,
                    'global' => true,
                    'filter' => new LocationId(123),
                ]),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryContentTypeFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'ContentType' => [],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new ContentTypeFacetBuilder(),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryCriterionFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Criterion' => [
                    'filter' => new LocationId(123),
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new CriterionFacetBuilder([
                    'filter' => new LocationId(123),
                ]),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryFieldFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Field' => [
                    'fieldPaths' => [
                        'description',
                    ],
                    'regex' => '/^Description starts with:.*/',
                    'sort' => 'COUNT_DESC',
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new FieldFacetBuilder([
                    'fieldPaths' => [
                        'description',
                    ],
                    'regex' => '/^Description starts with:.*/',
                    'sort' => FieldFacetBuilder::COUNT_DESC,
                ]),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryLocationFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Location' => [
                    'location' => new Location(),
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new LocationFacetBuilder([
                    'location' => new Location(),
                ]),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQuerySectionFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Section' => [],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new SectionFacetBuilder(),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryTermFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'Term' => [],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new TermFacetBuilder(),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryUserFacetBuilder(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'User' => [
                    'select' => 'MODIFIER',
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new UserFacetBuilder([
                    'type' => UserFacetBuilder::MODIFIER,
                ]),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryNoFacetBuilders(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [],
        ]);

        self::assertEquals($expectedQuery, $result);
    }

    public function testQueryMultipleFacetBuilders(): void
    {
        $inputArray = [
            'Filter' => [],
            'Query' => [],
            'FacetBuilders' => [
                'ContentType' => [],
                'User' => [
                    'select' => 'MODIFIER',
                ],
            ],
        ];

        $parser = $this->getParser();
        $result = $parser->parse($inputArray, $this->getParsingDispatcher());

        $expectedQuery = new Query([
            'facetBuilders' => [
                new ContentTypeFacetBuilder(),
                new UserFacetBuilder([
                    'type' => UserFacetBuilder::MODIFIER,
                ]),
            ],
        ]);

        self::assertEquals($expectedQuery, $result);
    }
}
