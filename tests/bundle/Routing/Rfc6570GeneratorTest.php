<?php

/*
 * Copied from ibexa/templated-uri-router (fork of hautelook/templated-uri-router).
 *
 * Copyright (c) Fabien Potencier <fabien@symfony.com>
 * Copyright (c) 2012 Hautelook
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ibexa\Tests\Bundle\Rest\Routing;

use Ibexa\Bundle\Rest\Routing\Rfc6570Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 */
final class Rfc6570GeneratorTest extends TestCase
{
    /**
     * @param array<string, mixed> $parameters
     */
    #[DataProvider('getTestPlaceholderData')]
    public function testPlaceholder(string $expected, array $parameters): void
    {
        $routes = $this->getRoutes(false);

        $router = new Rfc6570Generator($routes, new RequestContext());

        self::assertEquals($expected, $router->generate('foo', $parameters));
    }

    /**
     * @return list<array{string, array<string, mixed>}>
     */
    public static function getTestPlaceholderData(): array
    {
        return [
            ['/foo/foobar/{?bar}', ['foo' => 'foobar', 'bar' => 'barbar']],
            ['/foo/foobar/{?bar,paramTwo}', ['foo' => 'foobar', 'bar' => 'barbar', 'paramTwo' => 'paramTwo']],
            ['/foo/foobar/{?bar%3Aencoded}', ['foo' => 'foobar', 'bar:encoded' => 'barbar']],
            ['/foo/foobar/{?bar*}', ['foo' => 'foobar', 'bar' => []]],
            ['/foo/{placeholder}/{?bar}', ['foo' => '{placeholder}', 'bar' => 'barbar']],
        ];
    }

    public function testPlaceholderInStrictParameter(): void
    {
        $routes = $this->getRoutes(true);

        $generator = new Rfc6570Generator($routes, new RequestContext());

        self::assertEquals('/foo/{placeholder}/{?bar}', $generator->generate('foo', ['foo' => '{placeholder}', 'bar' => 'barbar']));
    }

    public function testNonStringValueInStrictParameter(): void
    {
        $routes = $this->getRoutes(true);

        $generator = new Rfc6570Generator($routes, new RequestContext());

        self::assertSame('/foo/123/{?bar}', $generator->generate('foo', ['foo' => 123, 'bar' => 'barbar']));
    }

    public function testStrictParameters(): void
    {
        $routes = $this->getRoutes(true);

        $router = new Rfc6570Generator($routes, new RequestContext());

        $this->expectException(InvalidParameterException::class);
        $router->generate('foo', ['foo' => 'foobar', 'bar' => 'barbar']);
    }

    public function testLooseParameters(): void
    {
        $routes = $this->getRoutes(true);

        $router = new Rfc6570Generator($routes, new RequestContext());
        $router->setStrictRequirements(null);

        self::assertEquals('/foo/foobar/{?bar}', $router->generate('foo', ['foo' => 'foobar', 'bar' => 'barbar']));
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function getRoutes(bool $isParamRequired = true): array
    {
        $regexp = $isParamRequired ? '\d+' : '.*';

        return [
            'foo' => [
                ['foo'],
                ['foo' => '123'],
                [['text', '/foo/{foo}/']],
                [
                    ['text', '/'],
                    ['variable', '/', $regexp, 'foo', true],
                    ['text', '/foo'],
                ],
                [],
                [],
            ],
        ];
    }
}
