<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Nyholm\Psr7\Request as HttpRequest;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class TestCase extends BaseTestCase
{
    public const array X_HTTP_METHOD_OVERRIDE_MAP = [
        'PUBLISH' => 'POST',
        'MOVE' => 'POST',
        'PATCH' => 'PATCH',
        'COPY' => 'POST',
    ];

    private Psr18Client $httpClient;

    private string $httpHost;

    /**
     * HTTP scheme (http or https).
     */
    private string $httpScheme;

    /**
     * Basic auth login:password.
     */
    private string $httpAuth;

    protected static $testSuffix;

    private array $headers = [];

    /**
     * The username to use for login.
     */
    private string $loginUsername;

    /**
     * The password to use for login.
     */
    private string $loginPassword;

    /**
     * If true, a login request is automatically done during setUp().
     */
    protected bool $autoLogin = true;

    /**
     * List of REST contentId (/content/objects/12345) created by tests.
     */
    private static array $createdContent = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpHost = getenv('EZP_TEST_REST_HOST') ?: 'localhost';
        $this->httpScheme = getenv('EZP_TEST_REST_SCHEME') ?: 'http';
        $this->httpAuth = getenv('EZP_TEST_REST_AUTH') ?: 'admin:publish';
        [$this->loginUsername, $this->loginPassword] = explode(':', $this->httpAuth);

        $this->httpClient = new Psr18Client(new CurlHttpClient([
            'verify_host' => false,
            'verify_peer' => false,
            'timeout' => 90,
            'max_redirects' => 0,
        ]));

        if ($this->autoLogin) {
            $session = $this->login();
            $this->headers['Cookie'] = sprintf('%s=%s', $session->name, $session->identifier);
            $this->headers['X-CSRF-Token'] = $session->csrfToken;
        }
    }

    public function createBrowser(): HttpBrowser
    {
        return new HttpBrowser(new CurlHttpClient());
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function sendHttpRequest(RequestInterface $request): ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }

    protected function getHttpHost(): string
    {
        return $this->httpHost;
    }

    protected function getLoginUsername(): string
    {
        return $this->loginUsername;
    }

    protected function getLoginPassword(): string
    {
        return $this->loginPassword;
    }

    /**
     * Get base URI for Browser based requests.
     */
    protected function getBaseURI(): string
    {
        return "{$this->httpScheme}://{$this->httpHost}";
    }

    /**
     * @param array $extraHeaders [key => value] array of extra headers
     */
    public function createHttpRequest(
        string $method,
        string $uri,
        string $contentType = '',
        string $acceptType = '',
        string $body = '',
        array $extraHeaders = []
    ): RequestInterface {
        $headers = array_merge(
            $method === 'POST' && $uri === '/api/ibexa/v2/user/sessions' ? [] : $this->headers,
            [
                'Content-Type' => $this->generateMediaTypeString($contentType),
                'Accept' => $this->generateMediaTypeString($acceptType),
            ]
        );

        if (isset(static::X_HTTP_METHOD_OVERRIDE_MAP[$method])) {
            $headers['X-HTTP-Method-Override'] = $method;
            $method = static::X_HTTP_METHOD_OVERRIDE_MAP[$method];
        }

        return new HttpRequest(
            $method,
            $this->getBaseURI() . $uri,
            array_merge($headers, $extraHeaders),
            $body
        );
    }

    protected function assertHttpResponseCodeEquals(ResponseInterface $response, int $expected): void
    {
        $responseCode = $response->getStatusCode();
        try {
            self::assertEquals($expected, $responseCode);
        } catch (ExpectationFailedException $e) {
            $errorMessageString = '';
            $contentTypeHeader = $response->hasHeader('Content-Type')
                ? $response->getHeader('Content-Type')[0]
                : '';

            if (strpos($contentTypeHeader, 'application/vnd.ibexa.api.ErrorMessage+xml') !== false) {
                $body = \simplexml_load_string($response->getBody());
                $errorMessageString = $this->getHttpResponseCodeErrorMessage($body);
            } elseif (strpos($contentTypeHeader, 'application/vnd.ibexa.api.ErrorMessage+json') !== false) {
                $body = json_decode($response->getBody());
                $errorMessageString = $this->getHttpResponseCodeErrorMessage($body->ErrorMessage);
            }

            self::assertEquals($expected, $responseCode, $errorMessageString);
        }
    }

    private function getHttpResponseCodeErrorMessage(mixed $errorMessage): string
    {
        $errorMessageString = <<< EOF
Server error message ({$errorMessage->errorCode}): {$errorMessage->errorMessage}

{$errorMessage->errorDescription}

EOF;

        // If server is in debug mode it will return file, line and trace.
        if (!empty($errorMessage->file)) {
            $errorMessageString .= "\nIn {$errorMessage->file}:{$errorMessage->line}\n\n{$errorMessage->trace}";
        } else {
            $errorMessageString .= "\nIn \<no trace, debug disabled\>";
        }

        return $errorMessageString;
    }

    protected function assertHttpResponseHasHeader(ResponseInterface $response, string $header, $expectedValue = null)
    {
        $headerValue = $response->hasHeader($header) ? $response->getHeader($header)[0] : null;
        self::assertNotNull($headerValue, "Failed asserting that response has a {$header} header");
        if ($expectedValue !== null) {
            self::assertEquals($expectedValue, $headerValue);
        }
    }

    protected function generateMediaTypeString(string $typeString): string
    {
        return "application/vnd.ibexa.api.$typeString";
    }

    protected function getMediaFromTypeString(string $typeString): string
    {
        $prefix = 'application/vnd.ibexa.api.';
        self::assertStringStartsWith(
            $prefix,
            $typeString,
            "Unknown media: {$typeString}"
        );

        return substr($typeString, strlen($prefix));
    }

    protected function addCreatedElement(string $href): void
    {
        $testCase = $this;
        self::$createdContent[$href] = static function () use ($href, $testCase): void {
            $testCase->sendHttpRequest(
                $testCase->createHttpRequest('DELETE', $href)
            );
        };
    }

    public static function tearDownAfterClass(): void
    {
        self::clearCreatedElement(self::$createdContent);
    }

    private static function clearCreatedElement(array $contentArray): void
    {
        foreach (array_reverse($contentArray) as $callback) {
            $callback();
        }
    }

    /**
     * @param string $string The value of the folders name field
     * @param string $parentLocationId The REST resource id of the parent location
     *
     * @return array created Content, as an array
     */
    protected function createFolder(
        string $string,
        string $parentLocationId,
        ?string $remoteId = null
    ): array {
        $string = $this->addTestSuffix($string);
        $remoteId = $remoteId ?? md5(uniqid($string, true));
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/api/ibexa/v2/content/types/1" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="{$parentLocationId}" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ibexa/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>{$remoteId}</remoteId>
  <User href="/api/ibexa/v2/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$string}</fieldValue>
    </field>
  </fields>
</ContentCreate>
XML;

        return $this->createContent($xml);
    }

    /**
     * @return array Content key of the Content struct array
     */
    protected function createContent(string $xml): array
    {
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/objects',
            'ContentCreate+xml',
            'Content+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);

        $content = json_decode($response->getBody(), true);

        if (!isset($content['Content']['CurrentVersion']['Version'])) {
            self::fail("Incomplete response (no version):\n" . $response->getBody() . "\n");
        }

        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('PUBLISH', $content['Content']['CurrentVersion']['Version']['_href'])
        );

        self::assertHttpResponseCodeEquals($response, 204);

        $this->addCreatedElement($content['Content']['_href']);

        return $content['Content'];
    }

    protected function getContentLocations(string $contentHref): array
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentHref/locations", '', 'LocationList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);
        $folderLocations = json_decode($response->getBody(), true);

        return $folderLocations;
    }

    protected function addTestSuffix(string $string): string
    {
        if (!isset(self::$testSuffix)) {
            /** @noinspection NonSecureUniqidUsageInspection */
            self::$testSuffix = uniqid('', true);
        }

        return $string . '_' . self::$testSuffix;
    }

    /**
     * Sends a login request to the REST server.
     *
     * @return \stdClass an object with the name, identifier, csrftoken properties
     */
    protected function login(): \stdClass
    {
        $request = $this->createAuthenticationHttpRequest($this->getLoginUsername(), $this->getLoginPassword());
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 201);

        return json_decode($response->getBody()->getContents(), false, JSON_THROW_ON_ERROR)->Session;
    }

    /**
     * @param array $extraHeaders extra [key => value] headers to be passed with the authentication request
     */
    protected function createAuthenticationHttpRequest(string $login, string $password, array $extraHeaders = []): RequestInterface
    {
        return $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/sessions',
            'SessionInput+json',
            'Session+json',
            sprintf('{"SessionInput": {"login": "%s", "password": "%s"}}', $login, $password),
            $extraHeaders
        );
    }
}
