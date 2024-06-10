<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class BinaryContentTest extends RESTFunctionalTestCase
{
    public function testCreateContentWithImageData(): string
    {
        $string = $this->addTestSuffix(__FUNCTION__);
        $pathToFile = __DIR__ . '/_fixtures/files/ibexa.png';
        $fileName = pathinfo($pathToFile, PATHINFO_BASENAME);
        $fileSize = filesize($pathToFile);
        $fileData = base64_encode(file_get_contents($pathToFile));

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/api/ibexa/v2/content/types/5" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ibexa/v2/content/locations/1/2" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ibexa/v2/content/sections/3" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>{$string}</remoteId>
  <User href="/api/ibexa/v2/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$string}</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>image</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>
            <value key="fileName">{$fileName}</value>
            <value key="fileSize">{$fileSize}</value>
            <value key="data">{$fileData}</value>
      </fieldValue>
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

        $this->assertHttpResponseCodeEquals($response, Response::HTTP_CREATED);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateContentWithImageData
     */
    public function testGetImageVariation(string $hrefToImage): void
    {
        $imageResponse = $this->sendHttpRequest(
            $this->createHttpRequest(
                'GET',
                $hrefToImage . '/versions/1',
                '',
                'Version+json'
            )
        );

        $jsonResponse = json_decode(
            $imageResponse->getBody()->getContents(),
            false,
            512,
            JSON_THROW_ON_ERROR
        );

        $imageField = $jsonResponse->Version->Fields->field[2];

        self::assertObjectHasProperty('variations', $imageField->fieldValue);

        $variationResponse = $this->sendHttpRequest(
            $this->createHttpRequest(
                'GET',
                $imageField->fieldValue->variations->medium->href,
            )
        );

        $this->assertHttpResponseCodeEquals($variationResponse, Response::HTTP_OK);
    }

    /**
     * @depends testCreateContentWithImageData
     */
    public function testGetImageAssetVariations(string $hrefToImage): void
    {
        $parsedHref = explode('/', $hrefToImage);
        $destinationContentId = end($parsedHref);

        $contentTypeHref = $this->createContentTypeWithImageAsset();

        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="{$contentTypeHref}" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ibexa/v2/content/locations/1/2" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ibexa/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <User href="/api/ibexa/v2/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>Test Image asset</fieldValue>
    </field>
     <field>
      <fieldDefinitionIdentifier>image_asset</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>
        <value key="destinationContentId">{$destinationContentId}</value>
        <value key="alternativeText">test</value>
      </fieldValue>
    </field>
  </fields>
</ContentCreate>
XML;
        $testContent = $this->createContent($xml);
        $imageField = $testContent['CurrentVersion']['Version']['Fields']['field'][1];

        self::assertArrayHasKey('variations', $imageField['fieldValue']);

        $variationResponse = $this->sendHttpRequest(
            $this->createHttpRequest(
                'GET',
                $imageField['fieldValue']['variations']['medium']['href'],
            )
        );
        $this->assertHttpResponseCodeEquals($variationResponse, Response::HTTP_OK);
    }

    private function createContentTypeWithImageAsset(): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>image_asset_ct</identifier>
  <names>
    <value languageCode="eng-GB">Image Asset content type</value>
  </names>
  <remoteId>image_asset_ct</remoteId>
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
      <fieldType>ezstring</fieldType>
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
      <identifier>image_asset</identifier>
      <fieldType>ezimageasset</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>2</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Image Asset</value>
      </names>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/typegroups/3/types?publish=true',
            'ContentTypeCreate+xml',
            'ContentType+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        $this->assertHttpResponseCodeEquals($response, 201);
        $this->assertHttpResponseHasHeader($response, 'Location');

        $this->addCreatedElement($response->getHeader('Location')[0]);

        return $response->getHeader('Location')[0];
    }
}
