<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView;

use DOMDocument;
use DOMElement;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;

class SearchViewTest extends SearchViewTestCase
{
    protected string $contentTypeHref;

    /** @var array<int, string> */
    protected array $contentHrefList;

    private string $nonSearchableContentHref;

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->contentTypeHref = $this->createTestContentType();
        $this->nonSearchableContentHref = $this->createContentWithUrlField();
        $this->contentHrefList[] = $this->createTestContentWithTags('test-name', ['foo', 'bar']);
        $this->contentHrefList[] = $this->createTestContentWithTags('fancy-name', ['baz', 'foobaz']);
        $this->contentHrefList[] = $this->createTestContentWithTags('even-fancier', ['bar', 'bazfoo']);
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        array_map([$this, 'deleteContent'], $this->contentHrefList);
        $this->deleteContent($this->contentTypeHref);
        $this->deleteContent($this->nonSearchableContentHref);
    }

    /**
     * Covers POST with ContentQuery Logic on /api/ibexa/v2/views using payload in the XML format.
     *
     * @dataProvider xmlProvider
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function testSimpleXmlContentQuery(string $xmlQueryBody, int $expectedCount): void
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
<identifier>your-query-id</identifier>
<public>false</public>
<ContentQuery>
  <Query>
    $xmlQueryBody
  </Query>  
  <limit>10</limit>  
  <offset>0</offset> 
</ContentQuery>
</ViewInput>
XML;

        self::assertEquals($expectedCount, $this->getQueryResultsCount('xml', $body));
    }

    public function testLocationsByIdQueryContainsCurrentVersionObject(): void
    {
        $body = <<<JSON
        {
            "ViewInput": {
                "identifier": "locations-by-id",
                "public": "false",
                "LocationQuery": {
                    "Filter": {
                        "LocationIdCriterion": "2"
                    },
                    "limit": "10",
                    "offset": "0"
                }
            }
        }
        JSON;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/views',
            'ViewInput+json; version=1.1',
            'View+json',
            $body
        );

        $response = $this->sendHttpRequest($request);
        $jsonResponse = json_decode($response->getBody()->getContents());

        $content = $jsonResponse->View->Result->searchHits->searchHit[0]->value->Location->ContentInfo->Content;

        self::assertIsObject($content->CurrentVersion->Version);
    }

    /**
     * Covers POST with LocationQuery Logic on /api/ibexa/v2/views using payload in the JSON format.
     *
     * @dataProvider jsonProvider
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function testSimpleJsonContentQuery(string $jsonQueryBody, int $expectedCount): void
    {
        $body = <<< JSON
{
    "ViewInput": {
        "identifier": "your-query-id",
        "public": "false",
        "LocationQuery": {
            "Filter": {
                $jsonQueryBody
            },
            "limit": "10",
            "offset": "0"
        }
    }
}
JSON;

        self::assertEquals($expectedCount, $this->getQueryResultsCount('json', $body));
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function createTestContentType(): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>tags-test</identifier>
  <names>
    <value languageCode="eng-GB">testContentQueryWithTags</value>
  </names>
  <remoteId>testContentQueryWithTags</remoteId>
  <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
  <nameSchema>&lt;title&gt;</nameSchema>
  <isContainer>true</isContainer>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
  <defaultSortField>PATH</defaultSortField>
  <defaultSortOrder>ASC</defaultSortOrder>
  <FieldDefinitions>
    <FieldDefinition>
      <identifier>title</identifier>
      <fieldType>ibexa_string</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <defaultValue>New Title</defaultValue>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Title</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">This is the title</value>
      </descriptions>
    </FieldDefinition>
    <FieldDefinition>
      <identifier>tags</identifier>
      <fieldType>ibexa_keyword</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>2</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Tags</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">Those are searchable tags</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/typegroups/1/types?publish=true',
            'ContentTypeCreate+xml',
            'ContentType+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * @param string[] $tags
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function createTestContentWithTags(string $name, array $tags): string
    {
        $tagsString = implode(',', $tags);
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="$this->contentTypeHref" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ibexa/v2/content/locations/1" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ibexa/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>$name</remoteId>
  <User href="/api/ibexa/v2/user/users/14" />
  <modificationDate>2018-01-30T18:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>$name</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>tags</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>$tagsString</fieldValue>
    </field>
    </fields>
</ContentCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/objects',
            'ContentCreate+xml',
            'ContentInfo+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseHasHeader($response, 'Location');
        $href = $response->getHeader('Location')[0];
        $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', "$href/versions/1")
        );

        return $href;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function deleteContent(string $href): void
    {
        $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $href)
        );
    }

    public function xmlProvider(): array
    {
        $fooTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'foo');
        $barTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'bar');
        $bazTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'baz');
        $foobazTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'foobaz');
        $foobazInTag = $this->buildFieldXml('tags', Operator::IN, ['foobaz']);
        $bazfooInTag = $this->buildFieldXml('tags', Operator::IN, ['bazfoo']);
        $fooAndBarInTag = $this->buildFieldXml('tags', Operator::IN, ['foo', 'bar']);

        return [
            [
                $this->getXmlString(
                    $this->wrapIn('AND', [$fooTag, $barTag])
                ),
                1,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('OR', [
                        $this->wrapIn('AND', [$fooTag, $barTag]),
                        $this->wrapIn('AND', [$bazTag, $foobazTag]),
                    ])
                ),
                2,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('AND', [
                        $this->wrapIn('NOT', [$fooTag]),
                        $barTag,
                    ])
                ),
                1,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('OR', [
                        $foobazInTag,
                        $bazfooInTag,
                    ])
                ),
                2,
            ],
            [
                $this->getXmlString($fooAndBarInTag),
                2,
            ],
        ];
    }

    public function jsonProvider(): array
    {
        return [
            [
                <<< JSON
"OR": {
    "ContentRemoteIdCriterion": [
        "test-name",
        "fancy-name"
    ]
}
JSON,
                2,
            ],
            [
                <<< JSON
"AND": {
    "OR": {
        "ContentRemoteIdCriterion": [
            "test-name",
            "fancy-name"
        ]
    },
    "ContentRemoteIdCriterion": "test-name"
}
JSON,
                1,
            ],
        ];
    }

    /**
     * @param string|string[] $value
     */
    private function buildFieldXml(string $name, string $operator, string|array $value): DOMElement
    {
        $xml = new DOMDocument();
        $element = $xml->createElement('Field');
        $element->appendChild(new DOMElement('name', $name));
        $element->appendChild(new DOMElement('operator', $operator));

        //Force xml array with one value
        if (is_array($value)) {
            if (count($value) === 1) {
                $valueWrapper = $xml->createElement('value');
                $valueWrapper->appendChild(new DOMElement('value', $value[0]));
                $element->appendChild($valueWrapper);
            } else {
                foreach ($value as $singleValue) {
                    $element->appendChild(new DOMElement('value', $singleValue));
                }
            }
        } else {
            $element->appendChild(new DOMElement('value', $value));
        }

        return $element;
    }

    private function wrapIn(string $logicalOperator, array $toWrap): DOMElement
    {
        $xml = new DOMDocument();
        $wrapper = $xml->createElement($logicalOperator);

        foreach ($toWrap as $field) {
            $innerWrapper = $xml->createElement($logicalOperator);
            $innerWrapper->appendChild($xml->importNode($field, true));
            $wrapper->appendChild($innerWrapper);
        }

        return $wrapper;
    }

    private function getXmlString(DOMElement $simpleXMLElement): string
    {
        return $simpleXMLElement->ownerDocument->saveXML($simpleXMLElement);
    }

    /**
     * This is just to assure that field with same name but without legacy search engine implementation
     * does not block search in different content type.
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function createContentWithUrlField(): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>rich-text-test</identifier>
  <names>
    <value languageCode="eng-GB">urlContentType</value>
  </names>
  <remoteId>testUrlContentType</remoteId>
  <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
  <nameSchema>&lt;title&gt;</nameSchema>
  <isContainer>true</isContainer>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
  <defaultSortField>PATH</defaultSortField>
  <defaultSortOrder>ASC</defaultSortOrder>
  <FieldDefinitions>
    <FieldDefinition>
      <identifier>title</identifier>
      <fieldType>ibexa_url</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <names>
        <value languageCode="eng-GB">Title</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">This is the title but in url type</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/typegroups/1/types?publish=true',
            'ContentTypeCreate+xml',
            'ContentType+json',
            $body
        );

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }
}
