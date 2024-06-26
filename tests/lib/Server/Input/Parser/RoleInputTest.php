<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Core\Repository\RoleService;
use Ibexa\Core\Repository\Values\User\RoleCreateStruct;
use Ibexa\Rest\Server\Input\Parser\RoleInput;

class RoleInputTest extends BaseTest
{
    /**
     * Tests the RoleInput parser.
     */
    public function testParse()
    {
        $inputArray = [
            'identifier' => 'Identifier Bar',
            /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
            'mainLanguageCode' => 'eng-GB',
            'names' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test role'
                    )
                )
            ),
            'descriptions' => array(
                'value' => array(
                    array(
                        '_languageCode' => 'eng-GB',
                        '#text' => 'Test role description'
                    )
                )
            )
            */
        ];

        $roleInput = $this->getParser();
        $result = $roleInput->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertInstanceOf(
            RoleCreateStruct::class,
            $result,
            'RoleCreateStruct not created correctly.'
        );

        self::assertEquals(
            'Identifier Bar',
            $result->identifier,
            'RoleCreateStruct identifier property not created correctly.'
        );

        /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        $this->assertEquals(
            array( 'eng-GB' => 'Test role' ),
            $result->names,
            'RoleCreateStruct names property not created correctly.'
        );

        $this->assertEquals(
            array( 'eng-GB' => 'Test role description' ),
            $result->descriptions,
            'RoleCreateStruct descriptions property not created correctly.'
        );
        */
    }

    /**
     * Returns the role input parser.
     *
     * @return \Ibexa\Rest\Server\Input\Parser\RoleInput
     */
    protected function internalGetParser()
    {
        return new RoleInput(
            $this->getRoleServiceMock(),
            $this->getParserTools()
        );
    }

    /**
     * Get the role service mock object.
     *
     * @return \Ibexa\Contracts\Core\Repository\RoleService
     */
    protected function getRoleServiceMock()
    {
        $roleServiceMock = $this->createMock(RoleService::class);

        $roleServiceMock->expects(self::any())
            ->method('newRoleCreateStruct')
            ->with(self::equalTo('Identifier Bar'))
            ->willReturn(
                new RoleCreateStruct(['identifier' => 'Identifier Bar'])
            );

        return $roleServiceMock;
    }
}
