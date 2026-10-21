<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values\CachedValue;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CachedValueTest extends ValueObjectVisitorBaseTest
{
    protected $options;

    protected $defaultOptions = [
        'content.view_cache' => true,
        'content.ttl_cache' => true,
        'content.default_ttl' => 60,
    ];

    /**
     * @var Request
     */
    protected $request;

    private const FULL_CACHE_HEADER_CALLS = ['setPublic', 'setVary Accept', 'setSharedMaxAge', 'setVary X-User-Hash'];

    public function setUp(): void
    {
        $this->request = new Request();
        $this->request->headers->set('X-User-Hash', 'blabla');
    }

    public function testVisit(): void
    {
        $calls = [];
        $this->expectFullCacheHeaders($calls);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotEmpty($result);
        self::assertSame(self::FULL_CACHE_HEADER_CALLS, $calls);
    }

    public function testVisitLocationCache(): void
    {
        $calls = [];
        $this->expectFullCacheHeaders($calls);

        $result = $this->visit(new CachedValue(new stdClass(), ['locationId' => 'testLocationId']));

        self::assertNotEmpty($result);
        self::assertSame(self::FULL_CACHE_HEADER_CALLS, $calls);
    }

    public function testVisitNoUserHash(): void
    {
        $this->request->headers->remove('X-User-Hash');

        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic');
        // no Vary header on X-User-Hash
        $responseMock->expects(self::once())->method('setVary')->with('Accept');
        $responseMock->expects(self::once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotEmpty($result);
    }

    public function testVisitNoRequest(): void
    {
        $this->request = null;

        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic');
        $responseMock->expects(self::once())->method('setVary')->with('Accept');
        $responseMock->expects(self::once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotEmpty($result);
    }

    public function testVisitViewCacheDisabled(): void
    {
        // disable caching globally
        $this->options = array_merge($this->defaultOptions, ['content.view_cache' => false]);

        $this->getResponseMock()->expects(self::never())->method('setPublic');

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotEmpty($result);
    }

    public function testVisitCacheTTLCacheDisabled(): void
    {
        // disable caching globally
        $this->options = array_merge($this->defaultOptions, ['content.ttl_cache' => false]);

        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic');
        $responseMock->expects(self::once())->method('setVary')->with('Accept');
        $responseMock->expects(self::never())->method('setSharedMaxAge');

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotEmpty($result);
    }

    /**
     * Expects the response calls and records them in $calls, to assert their order across methods.
     *
     * @param list<string> $calls
     */
    private function expectFullCacheHeaders(array &$calls): void
    {
        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic')
            ->willReturnCallback(static function () use (&$calls, $responseMock) {
                $calls[] = 'setPublic';

                return $responseMock;
            });
        $setVaryMatcher = self::exactly(2);
        $responseMock->expects($setVaryMatcher)->method('setVary')
            ->willReturnCallback(static function (...$parameters) use ($setVaryMatcher, &$calls, $responseMock) {
                if ($setVaryMatcher->getInvocationCount() === 1) {
                    self::assertSame(['Accept', true], $parameters);
                } else {
                    self::assertSame(['X-User-Hash', false], $parameters);
                }
                $calls[] = 'setVary ' . $parameters[0];

                return $responseMock;
            });
        $responseMock->expects(self::once())->method('setSharedMaxAge')
            ->with($this->defaultOptions['content.default_ttl'])
            ->willReturnCallback(static function () use (&$calls, $responseMock) {
                $calls[] = 'setSharedMaxAge';

                return $responseMock;
            });
    }

    protected function visit($value): string
    {
        $this->getVisitorMock()->expects(self::once())->method('visitValueObject')->with($value->value);

        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $value
        );

        return $generator->endDocument(null);
    }

    protected function internalGetVisitor(): ValueObjectVisitor\CachedValue
    {
        $visitor = new ValueObjectVisitor\CachedValue(
            $this->getConfigProviderMock()
        );
        $requestStack = new RequestStack();
        if ($this->request) {
            $requestStack->push($this->request);
        }
        $visitor->setRequestStack($requestStack);

        return $visitor;
    }

    protected function getConfigProviderMock(): ConfigResolverInterface & MockObject
    {
        $options = $this->options ?: $this->defaultOptions;

        $mock = $this->createMock(ConfigResolverInterface::class);
        $mock
            ->expects(self::any())
            ->method('hasParameter')
            ->willReturnCallback(
                static function ($parameterName) use ($options): bool {
                    return isset($options[$parameterName]);
                }
            );
        $mock
            ->expects(self::any())
            ->method('getParameter')
            ->willReturnCallback(
                static function (
                    $parameterName,
                    $defaultValue
                ) use ($options) {
                    return isset($options[$parameterName]) ? $options[$parameterName] : $defaultValue;
                }
            );

        return $mock;
    }
}
