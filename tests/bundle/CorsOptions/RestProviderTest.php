<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\CorsOptions;

use Exception;
use Ibexa\Bundle\Rest\CorsOptions\RestProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class RestProviderTest extends TestCase
{
    /**
     * Return value expectation for RequestMatcher::matchRequest
     * Set to false to expect Router::match() never to be called, or to an exception to have it throw one.
     */
    protected array $matchRequestResult = [];

    public function testGetOptions(): void
    {
        $this->matchRequestResult = ['allowedMethods' => 'GET,POST,DELETE'];

        self::assertEquals(
            [
                'allow_methods' => ['GET', 'POST', 'DELETE'],
            ],
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsResourceNotFound(): void
    {
        $this->matchRequestResult = new ResourceNotFoundException();
        self::assertEquals(
            [
                'allow_methods' => [],
            ],
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsMethodNotAllowed(): void
    {
        $this->matchRequestResult = new MethodNotAllowedException(['OPTIONS']);
        self::assertEquals(
            [
                'allow_methods' => [],
            ],
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsException(): void
    {
        $this->expectException(Exception::class);

        $this->matchRequestResult = new Exception();
        $this->getProvider()->getOptions($this->createRequest());
    }

    public function testGetOptionsNoMethods(): void
    {
        $this->matchRequestResult = [];
        self::assertEquals(
            [
                'allow_methods' => [],
            ],
            $this->getProvider()->getOptions($this->createRequest())
        );
    }

    public function testGetOptionsNotRestRequest(): void
    {
        $this->matchRequestResult = false;
        self::assertEquals(
            [],
            $this->getProvider()->getOptions($this->createRequest(false))
        );
    }

    /**
     * @param bool $isRestRequest whether or not to set the is_rest_request attribute
     */
    protected function createRequest(bool $isRestRequest = true): Request
    {
        $request = new Request();
        if ($isRestRequest) {
            $request->attributes->set('is_rest_request', true);
        }

        return $request;
    }

    protected function getProvider(): RestProvider
    {
        return new RestProvider(
            $this->getRequestMatcherMock()
        );
    }

    protected function getRequestMatcherMock(): RequestMatcherInterface&MockObject
    {
        $mock = $this->createMock(RequestMatcherInterface::class);

        if ($this->matchRequestResult instanceof Exception) {
            $mock->expects(self::any())
                ->method('matchRequest')
                ->will(self::throwException($this->matchRequestResult));
        } elseif ($this->matchRequestResult === false) {
            $mock->expects(self::never())
                ->method('matchRequest');
        } else {
            $mock->expects(self::any())
                ->method('matchRequest')
                ->willReturn($this->matchRequestResult);
        }

        return $mock;
    }
}
