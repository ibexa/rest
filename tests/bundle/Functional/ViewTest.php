<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Bundle\Rest\Functional;

class ViewTest extends TestCase
{
    use ResourceAssertionsTrait;

    private const VIEW_ENDPOINT_ACCEPT_TYPE = 'View+json';
    private const VIEW_ENDPOINT_URL = '/api/ibexa/v2/views';

    private const FORMAT_XML = 'xml';
    private const FORMAT_JSON = 'json';

    private const OPERATOR_EQUALITY = 'eq';
    private const OPERATOR_IN = 'in';

    private const CRITERION_LOCATION_DEPTH = 'LocationDepth';
    private const CRITERION_IS_MAIN_LOCATION = 'IsMainLocation';

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
            self::VIEW_ENDPOINT_URL,
            'ViewInput+xml',
            self::VIEW_ENDPOINT_ACCEPT_TYPE,
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
            self::VIEW_ENDPOINT_URL,
            "ViewInput+$type",
            self::VIEW_ENDPOINT_ACCEPT_TYPE,
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

        yield $template(self::CRITERION_LOCATION_DEPTH, self::OPERATOR_EQUALITY, self::FORMAT_XML) => [
            $this->loadFile(__DIR__ . '/_input/search/LocationDepth.eq.xml'),
            self::FORMAT_XML,
        ];

        yield $template(self::CRITERION_LOCATION_DEPTH, self::OPERATOR_EQUALITY, self::FORMAT_JSON) => [
            $this->loadFile(__DIR__ . '/_input/search/LocationDepth.eq.json'),
            self::FORMAT_JSON,
        ];

        yield $template(self::CRITERION_LOCATION_DEPTH, self::OPERATOR_IN, self::FORMAT_XML) => [
            $this->loadFile(__DIR__ . '/_input/search/LocationDepth.in.xml'),
            self::FORMAT_XML,
        ];

        yield $template(self::CRITERION_LOCATION_DEPTH, self::OPERATOR_IN, self::FORMAT_JSON) => [
            $this->loadFile(__DIR__ . '/_input/search/LocationDepth.in.json'),
            self::FORMAT_JSON,
        ];

        yield $template(self::CRITERION_IS_MAIN_LOCATION, self::OPERATOR_EQUALITY, self::FORMAT_XML) => [
            $this->loadFile(__DIR__ . '/_input/search/IsMainLocation.xml'),
            self::FORMAT_XML,
        ];

        yield $template(self::CRITERION_IS_MAIN_LOCATION, self::OPERATOR_EQUALITY, self::FORMAT_JSON) => [
            $this->loadFile(__DIR__ . '/_input/search/IsMainLocation.json'),
            self::FORMAT_JSON,
        ];
    }

    private function loadFile(string $filepath): string
    {
        $data = file_get_contents($filepath);

        if ($data === false) {
            throw new \RuntimeException(sprintf(
                'Unable to get contents for file: "%s". Ensure it exists and is readable.',
                $filepath,
            ));
        }

        return $data;
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
            self::VIEW_ENDPOINT_URL,
            'ViewInput+xml',
            self::VIEW_ENDPOINT_ACCEPT_TYPE,
            $body
        );
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);
        self::assertJsonResponseIsValid($response->getBody()->getContents(), 'View');
        $responseData = json_decode($response->getBody(), true);

        self::assertEquals(1, $responseData['View']['Result']['count']);
    }
}
