<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\RoleService;
use Ibexa\Core\Repository\Values\User\PolicyUpdateStruct;
use Ibexa\Rest\Server\Input\Parser\PolicyUpdate;
use PHPUnit\Framework\MockObject\MockObject;

class PolicyUpdateTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'limitations' => [
                'limitation' => [
                    [
                        '_identifier' => 'Class',
                        'values' => [
                            'ref' => [
                                [
                                    '_href' => 1,
                                ],
                                [
                                    '_href' => 2,
                                ],
                                [
                                    '_href' => 3,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $policyUpdate = $this->getParser();
        $result = $policyUpdate->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            PolicyUpdateStruct::class,
            $result,
            'PolicyUpdateStruct not created correctly.'
        );

        $parsedLimitations = $result->getLimitations();

        self::assertIsArray($parsedLimitations, 'PolicyUpdateStruct limitations not created correctly');

        self::assertCount(
            1,
            $parsedLimitations,
            'PolicyUpdateStruct limitations not created correctly'
        );

        self::assertInstanceOf(
            Limitation::class,
            $parsedLimitations['Class'],
            'Limitation not created correctly.'
        );

        self::assertEquals(
            'Class',
            $parsedLimitations['Class']->getIdentifier(),
            'Limitation identifier not created correctly.'
        );

        self::assertEquals(
            [1, 2, 3],
            $parsedLimitations['Class']->limitationValues,
            'Limitation values not created correctly.'
        );
    }

    public function testParseExceptionOnMissingLimitationIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'_identifier\' attribute for Limitation.');
        $inputArray = [
            'limitations' => [
                'limitation' => [
                    [
                        'values' => [
                            'ref' => [
                                [
                                    '_href' => 1,
                                ],
                                [
                                    '_href' => 2,
                                ],
                                [
                                    '_href' => 3,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $policyUpdate = $this->getParser();
        $policyUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingLimitationValues(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Invalid format for Limitation value in Limitation.');
        $inputArray = [
            'limitations' => [
                'limitation' => [
                    [
                        '_identifier' => 'Class',
                    ],
                ],
            ],
        ];

        $policyUpdate = $this->getParser();
        $policyUpdate->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): PolicyUpdate
    {
        return new PolicyUpdate(
            $this->getRoleServiceMock(),
            $this->getParserTools()
        );
    }

    protected function getRoleServiceMock(): RoleService & MockObject
    {
        $roleServiceMock = $this->createMock(RoleService::class);

        $roleServiceMock->expects(self::any())
            ->method('newPolicyUpdateStruct')
            ->willReturn(
                new PolicyUpdateStruct()
            );

        return $roleServiceMock;
    }
}
