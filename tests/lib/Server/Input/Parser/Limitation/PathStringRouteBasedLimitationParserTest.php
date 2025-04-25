<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Rest\Server\Input\Parser\Limitation\PathStringRouteBasedLimitationParser;
use Ibexa\Tests\Rest\Server\Input\Parser\BaseTest;

class PathStringRouteBasedLimitationParserTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            '_identifier' => 'Subtree',
            'values' => [
                'ref' => [
                    ['_href' => '/content/locations/1/2/3/4/'],
                ],
            ],
        ];

        $result = $this->getParser()->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(Limitation::class, $result);
        self::assertObjectHasAttribute('limitationValues', $result);
        self::assertArrayHasKey(0, $result->limitationValues);
        self::assertEquals('/1/2/3/4/', $result->limitationValues[0]);
    }

    protected function internalGetParser(): PathStringRouteBasedLimitationParser
    {
        return new PathStringRouteBasedLimitationParser('pathString', get_class(
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
            ['/content/locations/1/2/3/4/', 'pathString', '1/2/3/4/'],
        ];
    }
}
