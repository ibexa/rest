<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class ObjectStateTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/objectstategroups.
     *
     * @return string Object state group href
     */
    public function testCreateObjectStateGroup()
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ObjectStateGroupCreate>
  <identifier>testCreateObjectStateGroup</identifier>
  <defaultLanguageCode>eng-GB</defaultLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateObjectStateGroup</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateObjectStateGroup description</value>
  </descriptions>
</ObjectStateGroupCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/content/objectstategroups',
            'ObjectStateGroupCreate+xml',
            'ObjectStateGroup+json',
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
     * Covers POST /content/objectstategroups/{objectStateGroupId}/objectstates.
     *
     * @return string Object state href
     *
     * @depends testCreateObjectStateGroup
     */
    public function testCreateObjectState($objectStateGroupHref)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ObjectStateCreate>
  <identifier>testCreateObjectState</identifier>
  <priority>4</priority>
  <defaultLanguageCode>eng-GB</defaultLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateObjectState</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateObjectState description</value>
  </descriptions>
</ObjectStateCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$objectStateGroupHref/objectstates",
            'ObjectStateCreate+xml',
            'ObjectState+json',
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
     * Covers GET /content/objectstategroups/{objectStateGroupId}.
     *
     * @depends testCreateObjectStateGroup
     */
    public function testLoadObjectStateGroup(string $objectStateGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $objectStateGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/objectstategroups/{objectStateGroupId}/objectstates/{objectStateId}.
     *
     * @depends testCreateObjectState
     */
    public function testLoadObjectState(string $objectStateHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $objectStateHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/objectstategroups.
     */
    public function testLoadObjectStateGroups(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/objectstategroups')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /content/objectstategroups/{objectStateGroupId}/objectstates.
     *
     * @depends testCreateObjectStateGroup
     */
    public function testLoadObjectStates($objectStateGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$objectStateGroupHref/objectstates")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /content/objects/{contentId}/objectstates.
     *
     * @depends testCreateObjectState
     *
     * @return string The created folder content href
     */
    public function testSetObjectStatesForContent($objectStateHref)
    {
        $folder = $this->createFolder(__FUNCTION__, '/api/ibexa/v2/content/locations/1/2');

        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentObjectStates>
 <ObjectState href="$objectStateHref"/>
</ContentObjectStates>
XML;

        $folderHref = $folder['_href'];
        $request = $this->createHttpRequest(
            'PATCH',
            "$folderHref/objectstates",
            'ContentObjectStates+xml',
            'ContentObjectStates+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        return $folderHref;
    }

    /**
     * Covers GET /content/objects/{contentId}/objectstates.
     *
     * @depends testSetObjectStatesForContent
     */
    public function testGetObjectStatesForContent($contentHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$contentHref/objectstates")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /content/objectstategroups/{objectStateGroupId}/objectstates/{objectStateId}.
     *
     * @depends testCreateObjectState
     */
    public function testUpdateObjectState(string $objectStateHref): void
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ObjectStateUpdate>
  <identifier>testUpdateObjectState</identifier>
  <defaultLanguageCode>eng-GB</defaultLanguageCode>
  <names>
    <value languageCode="eng-GB">testUpdateObjectState</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testUpdateObjectState description</value>
  </descriptions>
</ObjectStateUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $objectStateHref,
            'ObjectStateUpdate+xml',
            'ObjectState+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /content/objectstategroups/{objectStateGroupId}.
     *
     * @depends testCreateObjectStateGroup
     */
    public function testUpdateObjectStateGroup(string $objectStateGroupHref): void
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ObjectStateGroupUpdate>
  <identifier>testUpdateObjectStateGroup</identifier>
  <defaultLanguageCode>eng-GB</defaultLanguageCode>
  <names>
    <value languageCode="eng-GB">testUpdateObjectStateGroup</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testUpdateObjectStateGroup description</value>
  </descriptions>
</ObjectStateGroupUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $objectStateGroupHref,
            'ObjectStateGroupUpdate+xml',
            'ObjectStateGroup+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE.
     *
     * @depends testCreateObjectState
     */
    public function testDeleteObjectState(string $objectStateHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $objectStateHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /content/objectstategroups/{objectStateGroupId}.
     *
     * @depends testCreateObjectStateGroup
     */
    public function testDeleteObjectStateGroup(string $objectStateGroupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $objectStateGroupHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }
}
