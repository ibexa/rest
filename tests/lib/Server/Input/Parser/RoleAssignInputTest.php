<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation\RoleLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\RoleAssignInput;
use Ibexa\Rest\Server\Values\RoleAssignment;

class RoleAssignInputTest extends BaseTest
{
    public function testParse(): void
    {
        $limitation = [
            '_identifier' => 'Section',
            'values' => [
                'ref' => [['_href' => '/content/sections/1']],
            ],
        ];

        $inputArray = [
            'Role' => ['_href' => '/user/roles/42'],
            'limitation' => $limitation,
        ];

        $this->getParsingDispatcherMock()
            ->expects(self::once())
            ->method('parse')
            ->with($limitation, 'application/vnd.ibexa.api.internal.limitation.Section')
            ->willReturn(new SectionLimitation());

        $roleAssignInput = $this->getParser();
        $result = $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            RoleAssignment::class,
            $result,
            'RoleAssignment not created correctly.'
        );

        self::assertEquals(
            '42',
            $result->roleId,
            'RoleAssignment roleId property not created correctly.'
        );

        self::assertInstanceOf(
            RoleLimitation::class,
            $result->limitation,
            'Limitation not created correctly.'
        );
    }

    public function testParseExceptionOnMissingRole(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'Role\' element for RoleAssignInput.');
        $inputArray = [
            'limitation' => [
                '_identifier' => 'Section',
                'values' => [
                    'ref' => [
                        [
                            '_href' => '/content/sections/1',
                        ],
                        [
                            '_href' => '/content/sections/2',
                        ],
                        [
                            '_href' => '/content/sections/3',
                        ],
                    ],
                ],
            ],
        ];

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnInvalidRole(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid \'Role\' element for RoleAssignInput.');
        $inputArray = [
            'Role' => [],
            'limitation' => [
                '_identifier' => 'Section',
                'values' => [
                    'ref' => [
                        [
                            '_href' => '/content/sections/1',
                        ],
                        [
                            '_href' => '/content/sections/2',
                        ],
                        [
                            '_href' => '/content/sections/3',
                        ],
                    ],
                ],
            ],
        ];

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingLimitationIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_identifier\' attribute for Limitation.');
        $inputArray = [
            'Role' => [
                '_href' => '/user/roles/42',
            ],
            'limitation' => [
                'values' => [
                    'ref' => [
                        [
                            '_href' => '/content/sections/1',
                        ],
                        [
                            '_href' => '/content/sections/2',
                        ],
                        [
                            '_href' => '/content/sections/3',
                        ],
                    ],
                ],
            ],
        ];

        $roleAssignInput = $this->getParser();
        $roleAssignInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): RoleAssignInput
    {
        return new RoleAssignInput(
            $this->getParserTools()
        );
    }

    public function getParseHrefExpectationsMap(): array
    {
        return [
            ['/user/roles/42', 'roleId', 42],
        ];
    }
}
