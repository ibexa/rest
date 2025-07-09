<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Input\Handler;

use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Rest\Input\Handler\Xml;
use PHPUnit\Framework\TestCase;

/**
 * Xml input handler test class.
 */
class XmlTest extends TestCase
{
    public function testConvertInvalidXml(): void
    {
        $this->expectException(Parser::class);

        $handler = new Xml();

        self::assertSame(
            [
                'text' => 'Hello world!',
            ],
            $handler->convert('{"text":"Hello world!"}')
        );
    }

    /**
     * @return array{(string|false), mixed}[]
     */
    public static function getXmlFixtures(): array
    {
        $fixtures = [];
        foreach (glob(__DIR__ . '/_fixtures/*.xml') as $xmlFile) {
            $fixtures[] = [
                (string)file_get_contents($xmlFile),
                is_file($xmlFile . '.php') ? include $xmlFile . '.php' : null,
            ];
        }

        return $fixtures;
    }

    /**
     * @dataProvider getXmlFixtures
     */
    public function testConvertXml(string $xml, mixed $expectation): void
    {
        $handler = new Xml();

        self::assertSame(
            $expectation,
            $handler->convert($xml)
        );
    }
}
