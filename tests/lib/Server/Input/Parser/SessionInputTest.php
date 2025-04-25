<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Server\Input\Parser\SessionInput;
use Ibexa\Rest\Server\Values\SessionInput as SessionInputValue;

class SessionInputTest extends BaseTest
{
    public function testParse(): void
    {
        $inputArray = [
            'login' => 'Login Foo',
            'password' => 'Password Bar',
        ];

        $sessionInput = $this->getParser();
        $result = $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());

        self::assertEquals(
            new SessionInputValue($inputArray),
            $result,
            'SessionInput not created correctly.'
        );
    }

    public function testParseExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'password\' attribute for SessionInput.');
        $inputArray = [
            'login' => 'Login Foo',
        ];

        $sessionInput = $this->getParser();
        $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    public function testParseExceptionOnMissingName(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'login\' attribute for SessionInput.');
        $inputArray = [
            'password' => 'Password Bar',
        ];

        $sessionInput = $this->getParser();
        $sessionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    protected function internalGetParser(): SessionInput
    {
        return new SessionInput();
    }
}
