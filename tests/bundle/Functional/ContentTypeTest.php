<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;
use PHPUnit\Framework\Attributes\Depends;

class ContentTypeTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/typegroups.
     */
    public function testCreateContentTypeGroup()
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>testCreateContentTypeGroup</identifier>
</ContentTypeGroupInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/typegroups',
            'ContentTypeGroupInput+xml',
            'ContentTypeGroup+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @return string the updated content type href
     * Covers PATCH /content/typegroups/<contentTypeGroupId>
     */
    #[Depends('testCreateContentTypeGroup')]
    public function testUpdateContentTypeGroup(string $contentTypeGroupHref): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeGroupInput>
  <identifier>testUpdateContentTypeGroup</identifier>
</ContentTypeGroupInput>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $contentTypeGroupHref,
            'ContentTypeGroupInput+xml',
            'ContentTypeGroup+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        return $contentTypeGroupHref;
    }

    /**
     * @returns string The created content type href
     * Covers POST /content/typegroups/<contentTypeGroupId>/types?publish=true
     *
     * @todo write test with full workflow (draft, edit, publish)
     */
    #[Depends('testCreateContentTypeGroup')]
    public function testCreateContentType($contentTypeGroupHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>tCreate</identifier>
  <names>
    <value languageCode="eng-GB">testCreateContentType</value>
  </names>
  <remoteId>testCreateContentType</remoteId>
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
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$contentTypeGroupHref/types?publish=true",
            'ContentTypeCreate+xml',
            'ContentType+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $this->addCreatedElement($response->getHeader('Location')[0]);

        return $response->getHeader('Location')[0];
    }

    /**
     * @param string $contentTypeGroupHref
     * Covers GET /content/typegroups/<contentTypeGroupId>
     */
    #[Depends('testCreateContentTypeGroup')]
    public function testListContentTypesForGroup($contentTypeGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', "$contentTypeGroupHref/types")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/typegroups.
     */
    public function testLoadContentTypeGroupList(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/typegroups')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        // @todo test data
    }

    /**
     * Covers GET /content/typegroups?identifier=<contentTypeGroupIdentifier>.
     */
    #[Depends('testUpdateContentTypeGroup')]
    public function testLoadContentTypeGroupListWithIdentifier(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/typegroups?identifier=testUpdateContentTypeGroup')
        );
        // @todo Check if list filtered by identifier is supposed to send a 307
        self::assertHttpResponseCodeEquals($response, 307);
    }

    /**
     * Covers GET /content/typegroups/<contentTypeGroupId>.
     */
    #[Depends('testUpdateContentTypeGroup')]
    public function testLoadContentTypeGroup(string $contentTypeGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @param string $contentTypeGroupHref
     * Covers GET /content/typegroups/<contentTypeGroupId>
     */
    #[Depends('testUpdateContentTypeGroup')]
    public function testLoadContentTypeGroupNotFound($contentTypeGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "{$contentTypeGroupHref}1234")
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * Covers GET /content/types/<contentTypeId>.
     */
    #[Depends('testCreateContentType')]
    public function testLoadContentType(string $contentTypeHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/types/<contentTypeId>.
     */
    #[Depends('testCreateContentType')]
    public function testLoadContentTypeNotFound($contentTypeHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "{$contentTypeHref}1234")
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * Covers GET /content/types.
     */
    #[Depends('testCreateContentType')]
    public function testListContentTypes(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/types')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/types?identifier=<contentTypeIdentifier>.
     */
    #[Depends('testCreateContentType')]
    public function testListContentTypesByIdentifier(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/types?identifier=tCreate')
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/types?remoteid=<contentTypeRemoteId>.
     */
    #[Depends('testCreateContentType')]
    public function testListContentTypesByRemoteId(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/types?remoteId=testCreateContentType')
        );

        // @todo This isn't consistent with the behaviour of /content/typegroups?identifier=
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @return string The copied content type href
     * Covers COPY /content/types/<contentTypeId>
     */
    #[Depends('testCreateContentType')]
    public function testCopyContentType(string $sourceContentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('COPY', $sourceContentTypeHref, '', 'ContentType+json')
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;

        // @todo test identifier (copy_of_<originalBaseIdentifier>_<newTypeId>)
    }

    /**
     * Covers POST /content/type/<contentTypeId>.
     *
     *
     * @return string the created content type draft href
     */
    #[Depends('testCopyContentType')]
    public function testCreateContentTypeDraft(string $contentTypeHref)
    {
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeUpdate>
  <names>
    <value languageCode="eng-GB">testCreateContentTypeDraft</value>
  </names>
</ContentTypeUpdate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            $contentTypeHref,
            'ContentTypeUpdate+xml',
            'ContentTypeInfo+json',
            $content
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * Covers GET /content/types/<contentTypeId>/draft.
     */
    #[Depends('testCreateContentTypeDraft')]
    public function testLoadContentTypeDraft(string $contentTypeDraftHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $contentTypeDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /content/types/<contentTypeId>/draft.
     */
    #[Depends('testCreateContentTypeDraft')]
    public function testUpdateContentTypeDraft(string $contentTypeDraftHref): void
    {
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeUpdate>
  <names>
    <value languageCode="eng-GB">testUpdateContentTypeDraft</value>
  </names>
</ContentTypeUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $contentTypeDraftHref,
            'ContentTypeUpdate+xml',
            'ContentTypeInfo+json',
            $content
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /content/types/<contentTypeId>/draft/fielddefinitions.
     *
     *
     * @return string The content type draft field definition href
     */
    #[Depends('testCreateContentTypeDraft')]
    public function testAddContentTypeDraftFieldDefinition($contentTypeDraftHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<FieldDefinition>
      <identifier>secondtext</identifier>
      <fieldType>ibexa_string</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <defaultValue>Second text</defaultValue>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Second text</value>
      </names>
    </FieldDefinition>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$contentTypeDraftHref/fieldDefinitions",
            'FieldDefinitionCreate+xml',
            'FieldDefinition+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * @return string the href of the first field definition in the list
     * Covers GET /content/types/<contentTypeId>/fieldDefinitions
     */
    #[Depends('testCreateContentType')]
    public function testContentTypeLoadFieldDefinitionList($contentTypeHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentTypeHref/fieldDefinitions", '', 'FieldDefinitionList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $data = json_decode($response->getBody(), true);

        return $data['FieldDefinitions']['FieldDefinition'][0]['_href'];
    }

    /**
     * @throws \Psr\Http\Client\ClientException
     * Covers GET /content/types/<contentTypeId>/fieldDefinitions/<fieldDefinitionId>
     */
    #[Depends('testAddContentTypeDraftFieldDefinition')]
    public function testLoadContentTypeFieldDefinition(string $fieldDefinitionHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $fieldDefinitionHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/types/{contentTypeId}/fieldDefinition/{fieldDefinitionIdentifier}.
     *
     *
     * @throws \Psr\Http\Client\ClientException
     */
    #[Depends('testCreateContentType')]
    public function testLoadContentTypeFieldDefinitionByIdentifier(string $contentTypeHref): void
    {
        $url = sprintf('%s/fieldDefinition/title', $contentTypeHref);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $url, '', 'FieldDefinition+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $data = json_decode($response->getBody(), true);

        self::assertEquals($url, $data['FieldDefinition']['_href']);
        self::assertEquals('title', $data['FieldDefinition']['identifier']);
    }

    /**
     * @todo the spec says PUT...
     * Covers PATCH /content/types/<contentTypeId>/fieldDefinitions/<fieldDefinitionId>
     */
    #[Depends('testAddContentTypeDraftFieldDefinition')]
    public function testUpdateContentTypeDraftFieldDefinition(string $fieldDefinitionHref): void
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<FieldDefinitionUpdate>
  <identifier>updated_secondtext</identifier>
  <names>
    <value languageCode="eng-GB">Updated second text</value>
  </names>
  <defaultValue>Updated default value</defaultValue>
</FieldDefinitionUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $fieldDefinitionHref,
            'FieldDefinitionUpdate+xml',
            'FieldDefinition+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE /content/types/<contentTypeId>/draft/fieldDefinitions/<fieldDefinitionId>.
     */
    #[Depends('testAddContentTypeDraftFieldDefinition')]
    public function deleteContentTypeDraftFieldDefinition(string $fieldDefinitionHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $fieldDefinitionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /content/types/<contentTypeId>/draft.
     */
    #[Depends('testCreateContentTypeDraft')]
    public function testDeleteContentTypeDraft(string $contentTypeDraftHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers PUBLISH /content/types/<contentTypeId>/draft.
     */
    #[Depends('testCreateContentType')]
    public function testPublishContentTypeDraft(string $contentTypeHref): void
    {
        // we need to create a content type draft first since we deleted the previous one in testDeleteContentTypeDraft
        $contentTypeDraftHref = $this->testCreateContentTypeDraft($contentTypeHref);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', $contentTypeDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/types/<contentTypeId>/groups.
     */
    #[Depends('testCreateContentType')]
    public function testLoadGroupsOfContentType($contentTypeHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentTypeHref/groups", '', 'ContentTypeGroupRefList+json')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @return string the content type href
     * Covers POST /content/types/<contentTypeId>/groups
     */
    #[Depends('testCreateContentType')]
    public function testLinkContentTypeToGroup($contentTypeHref)
    {
        // @todo Spec example is invalid, missing parameter name
        $request = $this->createHttpRequest('POST', "$contentTypeHref/groups?group=/api/ibexa/v2/content/typegroups/1");
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);

        return $contentTypeHref;
    }

    /**
     * Covers DELETE /content/types/{contentTypeId}/groups/{contentTypeGroupId}.
     */
    #[Depends('testLinkContentTypeToGroup')]
    public function testUnlinkContentTypeFromGroup($contentTypeHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "$contentTypeHref/groups/1")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    #[Depends('testCreateContentType')]
    public function testDeleteContentType(string $contentTypeHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /content/typegroups/<contentTypeGroupId>.
     */
    #[Depends('testCreateContentTypeGroup')]
    public function testDeleteContentTypeGroupNotEmpty(string $contentTypeGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $contentTypeGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 403);
    }

    public function testCreateView(): void
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>ContentTypeView</identifier>
  <ContentTypeQuery>
    <Query>
         <ContentTypeIdentifierCriterion>folder</ContentTypeIdentifierCriterion>
    </Query>
    <limit>10</limit>
    <offset>0</offset>
  </ContentTypeQuery>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/types/view',
            'ContentTypeViewInput+xml',
            'ContentTypeView+json',
            $body
        );

        $response = $this->sendHttpRequest($request);
        $responseData = json_decode($response->getBody(), true);

        self::assertArrayHasKey('ContentTypeList', $responseData);
        self::assertSame('folder', $responseData['ContentTypeList']['ContentType'][0]['identifier']);
    }

    public function testCreateViewWithContentTypeGroupName(): void
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>ContentTypeView</identifier>
  <ContentTypeQuery>
    <Query>
         <ContentTypeGroupNameCriterion>media</ContentTypeGroupNameCriterion>
    </Query>
    <limit>10</limit>
    <offset>0</offset>
  </ContentTypeQuery>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/types/view',
            'ContentTypeViewInput+xml',
            'ContentTypeView+json',
            $body
        );

        $response = $this->sendHttpRequest($request);
        $responseData = json_decode($response->getBody(), true);

        self::assertArrayHasKey('ContentTypeList', $responseData);
        self::assertSame('image', $responseData['ContentTypeList']['ContentType'][0]['identifier']);
    }
}
