<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Rest\Server\Input\Parser\Limitation\RouteBasedLimitationParser;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTest;

class RouteBasedLimitationParserTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            '_identifier' => 'Section',
            'values' => [
                'ref' => [
                    ['_href' => '/content/sections/42'],
                ],
            ],
        ];

        $result = $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(Limitation::class, $result);
        self::assertObjectHasAttribute('limitationValues', $result);
        self::assertArrayHasKey(0, $result->limitationValues);
        self::assertEquals(42, $result->limitationValues[0]);
    }

    /**
     * Must return the tested parser object.
     */
    protected function internalGetParser(): RouteBasedLimitationParser
    {
        return new RouteBasedLimitationParser('sectionId', get_class(
            new class() extends Limitation {
                public function getIdentifier(): string
                {
                    return 'identifier';
                }
            }
        ));
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/content/sections/42', 'sectionId', 42],
        ];
    }
}
