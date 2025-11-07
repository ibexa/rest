<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

final class SessionTest extends TestCase
{
    public function setUp(): void
    {
        $this->autoLogin = false;

        parent::setUp();
    }

    public function testCreateSessionBadCredentials(): void
    {
        $request = $this->createAuthenticationHttpRequest('admin', 'bad_password');
        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, 401);
    }

    /**
     * @depends testCreateSession
     */
    public function testRefreshSession(stdClass $session): void
    {
        $response = $this->sendHttpRequest($this->createRefreshRequest($session));

        $this->assertHttpResponseCodeEquals($response, 200);
    }

    public function testRefreshSessionExpired(): void
    {
        $session = $this->login();

        $response = $this->sendHttpRequest($this->createDeleteRequest($session));
        $this->assertHttpResponseCodeEquals($response, 204);

        $response = $this->sendHttpRequest($this->createRefreshRequest($session));
        $this->assertHttpResponseCodeEquals($response, 404);

        self::assertHttpResponseDeletesSessionCookie($session, $response);
    }

    public function testRefreshSessionMissingCsrfToken(): void
    {
        $session = $this->login();

        $refreshRequest = $this
            ->createRefreshRequest($session)
            ->withoutHeader('X-CSRF-Token');
        $response = $this->sendHttpRequest($refreshRequest);

        $this->assertHttpResponseCodeEquals($response, 401);
    }

    public function testCreateSession(): stdClass
    {
        return $this->login();
    }

    public function testDeleteSession(): void
    {
        $session = $this->login();
        $response = $this->sendHttpRequest($this->createDeleteRequest($session));

        $this->assertHttpResponseCodeEquals($response, 204);
        self::assertHttpResponseDeletesSessionCookie($session, $response);
    }

    /**
     * CSRF needs to be tested as session handling bypasses the CsrfListener.
     */
    public function testDeleteSessionMissingCsrfToken(): void
    {
        $session = $this->login();
        $request = $this
            ->createDeleteRequest($session)
            ->withoutHeader('X-CSRF-Token');
        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, 401);
    }

    public function testLoginWithExistingFrontendSession(): void
    {
        $baseURI = $this->getBaseURI();
        $browser = $this->createBrowser();

        $browser->request('GET', "{$baseURI}/login");
        $response = $browser->getInternalResponse();

        self::assertEquals(200, $response->getStatusCode());

        $domDocument = new DOMDocument();
        // load HTML, suppress error reporting due to buggy Sf toolbar code in dev/behat ENVs
        $domDocument->loadHTML($response->getContent(), LIBXML_NOERROR);

        $xpath = new DOMXPath($domDocument);

        $csrfDomElements = $xpath->query("//input[@name='_csrf_token']/@value");
        self::assertInstanceOf(DOMNodeList::class, $csrfDomElements);
        self::assertGreaterThan(0, $csrfDomElements->length);
        $item = $csrfDomElements->item(0);
        self::assertInstanceOf(DOMNode::class, $item);
        $csrfTokenValue = $item->nodeValue;

        $browser->followRedirects(false);
        $browser->submitForm(
            'Login',
            [
                '_username' => $this->getLoginUsername(),
                '_password' => $this->getLoginPassword(),
                '_csrf_token' => $csrfTokenValue,
            ]
        );
        $loginResponse = $browser->getInternalResponse();
        self::assertNotEmpty($loginResponse->getHeader('set-cookie'));

        $request = $this->createAuthenticationHttpRequest(
            $this->getLoginUsername(),
            $this->getLoginPassword(),
            ['Cookie' => $loginResponse->getHeader('set-cookie')[0]]
        );
        $response = $this->sendHttpRequest($request);

        // Session is recreated when using CSRF, expect 201 instead of 200
        $this->assertHttpResponseCodeEquals($response, 201);
    }

    public function testDeleteSessionExpired(): void
    {
        $session = $this->login();
        $deleteSessionRequest = $this->createDeleteRequest($session);

        $response = $this->sendHttpRequest($deleteSessionRequest);

        $this->assertHttpResponseCodeEquals($response, 204);
        self::assertHttpResponseDeletesSessionCookie($session, $response);

        // Triggered again to make sure deleting already deleted session results in 404
        $response = $this->sendHttpRequest($deleteSessionRequest);

        $this->assertHttpResponseCodeEquals($response, 404);
    }

    protected function createRefreshRequest(stdClass $session): RequestInterface
    {
        return $this->createHttpRequest(
            'POST',
            sprintf('/api/ibexa/v2/user/sessions/%s/refresh', $session->identifier),
            '',
            'Session+json',
            '',
            [
                'Cookie' => sprintf('%s=%s', $session->name, $session->identifier),
                'X-CSRF-Token' => $session->csrfToken,
            ]
        );
    }

    /**
     * @depends testCreateSession
     */
    public function testCheckSession(): void
    {
        $session = $this->login();
        $request = $this->createHttpRequest(
            'GET',
            '/api/ibexa/v2/user/sessions/current',
            '',
            'Session+json',
            '',
            [
                'Cookie' => sprintf('%s=%s', $session->name, $session->identifier),
                'X-CSRF-Token' => $session->csrfToken,
            ]
        );

        $response = $this->sendHttpRequest($request);
        $this->assertHttpResponseCodeEquals($response, 200);

        $contents = $response->getBody()->getContents();
        $data = json_decode($contents, true, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('Session', $data);
    }

    /**
     * @depends testCreateSession
     */
    public function testCheckSessionWithoutOne(): void
    {
        $request = $this->createHttpRequest(
            'GET',
            '/api/ibexa/v2/user/sessions/current',
            '',
            'Session+json'
        );

        $response = $this->sendHttpRequest($request);
        $this->assertHttpResponseCodeEquals($response, 404);

        $contents = $response->getBody()->getContents();
        self::assertEmpty($contents);
    }

    private function createDeleteRequest(stdClass $session): RequestInterface
    {
        return $this->createHttpRequest(
            'DELETE',
            $session->_href,
            '',
            '',
            '',
            [
                'Cookie' => sprintf('%s=%s', $session->name, $session->identifier),
                'X-CSRF-Token' => $session->csrfToken,
            ]
        );
    }

    private static function assertHttpResponseDeletesSessionCookie(
        stdClass $session,
        ResponseInterface $response
    ): void {
        self::assertStringStartsWith("{$session->name}=deleted;", $response->getHeader('set-cookie')[0]);
    }
}
