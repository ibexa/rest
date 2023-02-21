<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Rest\Functional;

class ViewTest extends TestCase
{
    use ResourceAssertionsTrait;

    /**
     * Covers POST /views.
     */
    public function testViewRequestWithOrStatement(): void
    {
        $fooRemoteId = md5('View test content foo');
        $barRemoteId = md5('View test content bar');
        $this->createFolder('View test content foo', '/api/ibexa/v2/content/locations/1/2', $fooRemoteId);
        $this->createFolder('View test content bar', '/api/ibexa/v2/content/locations/1/2', $barRemoteId);

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TitleView</identifier>
  <Query>
    <Filter>
        <OR>
            <ContentRemoteIdCriterion>{$fooRemoteId}</ContentRemoteIdCriterion>
            <ContentRemoteIdCriterion>{$barRemoteId}</ContentRemoteIdCriterion>
        </OR>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/views',
            'ViewInput+xml',
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        self::assertJsonResponseIsValid($response->getBody()->getContents(), 'View');
        $responseData = json_decode($response->getBody(), true);

        self::assertEquals(2, $responseData['View']['Result']['count']);
    }

    /**
     * @dataProvider provideForViewTest
     */
    public function testCriterions(string $body, string $type): void
    {
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/views',
            "ViewInput+$type",
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        self::assertJsonResponseIsValid($response->getBody()->getContents(), 'View');
        $responseData = json_decode($response->getBody(), true);
        self::assertGreaterThan(0, $responseData['View']['Result']['count']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function provideForViewTest(): iterable
    {
        $template = static fn (string $criterion, string $operator, string $format): string => sprintf(
            'Criterion: %s / Operator: %s / Format: %s',
            $criterion,
            strtoupper($operator),
            strtoupper($format),
        );

        yield $template('LocationDepth', 'eq', 'xml') => [
            file_get_contents(__DIR__ . '/_input/search/LocationDepth.eq.xml'),
            'xml',
        ];

        yield $template('LocationDepth', 'eq', 'json') => [
            file_get_contents(__DIR__ . '/_input/search/LocationDepth.eq.json'),
            'json',
        ];

        yield $template('LocationDepth', 'in', 'xml') => [
            file_get_contents(__DIR__ . '/_input/search/LocationDepth.in.xml'),
            'xml',
        ];

        yield $template('LocationDepth', 'in', 'json') => [
            file_get_contents(__DIR__ . '/_input/search/LocationDepth.in.json'),
            'json',
        ];

        yield $template('IsMainLocation', 'eq', 'xml') => [
            file_get_contents(__DIR__ . '/_input/search/IsMainLocation.xml'),
            'xml',
        ];

        yield $template('IsMainLocation', 'eq', 'json') => [
            file_get_contents(__DIR__ . '/_input/search/IsMainLocation.json'),
            'json',
        ];
    }

    /**
     * Covers POST /views.
     *
     * @depends testViewRequestWithOrStatement
     */
    public function testViewRequestWithAndStatement(): void
    {
        $fooRemoteId = md5('View test content foo');
        $barRemoteId = md5('View test content bar');

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>TitleView</identifier>
  <Query>
    <Filter>
        <AND>
            <OR>
                <ContentRemoteIdCriterion>{$fooRemoteId}</ContentRemoteIdCriterion>
                <ContentRemoteIdCriterion>{$barRemoteId}</ContentRemoteIdCriterion>
            </OR>
            <ContentRemoteIdCriterion>{$fooRemoteId}</ContentRemoteIdCriterion>
        </AND>
    </Filter>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/views',
            'ViewInput+xml',
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        self::assertJsonResponseIsValid($response->getBody()->getContents(), 'View');
        $responseData = json_decode($response->getBody(), true);

        self::assertEquals(1, $responseData['View']['Result']['count']);
    }
}
