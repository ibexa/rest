<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Input\Handler;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Input\Handler\Json;
use PHPUnit\Framework\TestCase;

/**
 * Json input handler test.
 */
class JsonTest extends TestCase
{
    public function testConvertInvalidJson(): void
    {
        $this->expectException(Parser::class);

        $handler = $this->getHandler();
        $handler->convert('{text:"Hello world!"}');
    }

    /**
     * Tests conversion of array to JSON.
     */
    public function testConvertJson(): void
    {
        $handler = $this->getHandler();

        self::assertSame(
            [
                'text' => 'Hello world!',
            ],
            $handler->convert('{"text":"Hello world!"}')
        );
    }

    public function testConvertFieldValue(): void
    {
        $handler = $this->getHandler();

        self::assertSame(
            [
                'Field' => [
                    'fieldValue' => [
                        [
                            'id' => 1,
                            'name' => 'Joe Sindelfingen',
                            'email' => 'sindelfingen@example.com',
                        ],
                        [
                            'id' => 2,
                            'name' => 'Joe Bielefeld',
                            'email' => 'bielefeld@example.com',
                        ],
                    ],
                ],
            ],
            $handler->convert(
                '{"Field":{"fieldValue":[{"id":1,"name":"Joe Sindelfingen","email":"sindelfingen@example.com"},{"id":2,"name":"Joe Bielefeld","email":"bielefeld@example.com"}]}}'
            )
        );
    }

    protected function getHandler(): Json
    {
        return new Json();
    }
}
