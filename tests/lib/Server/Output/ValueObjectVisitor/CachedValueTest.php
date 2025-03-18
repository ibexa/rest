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
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function setUp(): void
    {
        $this->request = new Request();
        $this->request->headers->set('X-User-Hash', 'blabla');
    }

    public function testVisit(): void
    {
        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic');
        $responseMock->expects(self::at(1))->method('setVary')->with('Accept');
        $responseMock->expects(self::once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);
        $responseMock->expects(self::at(3))->method('setVary')->with('X-User-Hash', false);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    public function testVisitLocationCache(): void
    {
        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic');
        $responseMock->expects(self::at(1))->method('setVary')->with('Accept');
        $responseMock->expects(self::once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);
        $responseMock->expects(self::at(3))->method('setVary')->with('X-User-Hash', false);

        $result = $this->visit(new CachedValue(new stdClass(), ['locationId' => 'testLocationId']));

        self::assertNotNull($result);
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

        self::assertNotNull($result);
    }

    public function testVisitNoRequest(): void
    {
        $this->request = null;

        $responseMock = $this->getResponseMock();
        $responseMock->expects(self::once())->method('setPublic');
        $responseMock->expects(self::once())->method('setVary')->with('Accept');
        $responseMock->expects(self::once())->method('setSharedMaxAge')->with($this->defaultOptions['content.default_ttl']);

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
    }

    public function testVisitViewCacheDisabled(): void
    {
        // disable caching globally
        $this->options = array_merge($this->defaultOptions, ['content.view_cache' => false]);

        $this->getResponseMock()->expects(self::never())->method('setPublic');

        $result = $this->visit(new CachedValue(new stdClass()));

        self::assertNotNull($result);
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

        self::assertNotNull($result);
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

    /**
     * Must return an instance of the tested visitor object.
     *
     * @return \Ibexa\Contracts\Rest\Output\ValueObjectVisitor
     */
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

    /**
     * @return \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConfigProviderMock(): MockObject
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
                static function ($parameterName, $defaultValue) use ($options) {
                    return isset($options[$parameterName]) ? $options[$parameterName] : $defaultValue;
                }
            );

        return $mock;
    }
}
