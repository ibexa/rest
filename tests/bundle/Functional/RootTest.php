<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;
use Ibexa\Tests\Rest\AssertXmlTagTrait;
use Psr\Http\Message\StreamInterface;

class RootTest extends RESTFunctionalTestCase
{
    use AssertXmlTagTrait;

    /**
     * Covers GET /.
     */
    public function testLoadRootResource(): StreamInterface
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        return $response->getBody();
    }

    public function testExpectedUser(): void
    {
        $request = $this->createHttpRequest('GET', '/api/ibexa/v2/');
        $request = $request->withHeader('Accept', 'application/json');
        $request = $request->withHeader('X-Expected-User', '');
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        $request = $request->withHeader('X-Expected-User', 'admin');
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        $request = $request->withHeader('X-Expected-User', 'foo');
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 401);
        $responseArray = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('ErrorMessage', $responseArray);
        self::assertSame('Expectation failed. User changed.', $responseArray['ErrorMessage']['errorDescription']);
    }

    /**
     * @dataProvider getRandomUriSet
     * Covers GET /<wrongUri>
     */
    public function testCatchAll(string $uri): void
    {
        self::markTestSkipped('@todo fixme');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/' . uniqid('rest', true), '', 'Stuff+json')
        );
        self::assertHttpResponseCodeEquals($response, 404);
        $responseArray = json_decode($response->getBody(), true);
        self::assertArrayHasKey('ErrorMessage', $responseArray);
        self::assertEquals('No such route', $responseArray['ErrorMessage']['errorDescription']);
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootElement($result): void
    {
        $this->assertXMLTag(
            ['tag' => 'Root'],
            $result,
            'Invalid <Root> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testLoadRootResource
     */
    public function testResultContainsRootAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Root',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Root+xml',
                ],
            ],
            $result,
            'Invalid <Root> attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'content',
            ],
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'content',
                'attributes' => [
                    'media-type' => '',
                    'href' => '/api/ibexa/v2/content/objects',
                ],
            ],
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentByRemoteIdTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentByRemoteId',
            ],
            $result,
            'Missing <contentByRemoteId> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentByRemoteIdTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentByRemoteId',
                'attributes' => [
                    'media-type' => '',
                    'href' => '/api/ibexa/v2/content/objects{?remoteId}',
                ],
            ],
            $result,
            'Invalid <contentByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypesTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypes',
            ],
            $result,
            'Invalid <contentTypes> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypesTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypes',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeInfoList+xml',
                    'href' => '/api/ibexa/v2/content/types',
                ],
            ],
            $result,
            'Invalid <content> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeByIdentifierTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypeByIdentifier',
            ],
            $result,
            'Invalid <contentTypeByIdentifier> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeByIdentifierTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypeByIdentifier',
                'attributes' => [
                    'media-type' => '',
                    'href' => '/api/ibexa/v2/content/types{?identifier}',
                ],
            ],
            $result,
            'Invalid <contentTypeByIdentifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeGroupsTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypeGroups',
            ],
            $result,
            'Missing <contentTypeGroups> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeGroupsTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypeGroups',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ContentTypeGroupList+xml',
                    'href' => '/api/ibexa/v2/content/typegroups',
                ],
            ],
            $result,
            'Invalid <contentTypeGroups> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeGroupByIdentifierTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypeGroupByIdentifier',
            ],
            $result,
            'Missing <ContentTypeGroupByIdentifier> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsContentTypeGroupByIdentifierTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'contentTypeGroupByIdentifier',
                'attributes' => [
                    'media-type' => '',
                    'href' => '/api/ibexa/v2/content/typegroups{?identifier}',
                ],
            ],
            $result,
            'Invalid <contentTypeGroupByIdentifier> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'users',
            ],
            $result,
            'Invalid <users> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'users',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                    'href' => '/api/ibexa/v2/user/users',
                ],
            ],
            $result,
            'Invalid <users> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRoleIdentifierTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByRoleId',
            ],
            $result,
            'Missing <usersByRoleId> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRoleIdentifierTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByRoleId',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                    'href' => '/api/ibexa/v2/user/users{?roleId}',
                ],
            ],
            $result,
            'Invalid <usersByRoleId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRemoteIdentifierTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByRemoteId',
            ],
            $result,
            'Missing <usersByRemoteId> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByRemoteIdentifierTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByRemoteId',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                    'href' => '/api/ibexa/v2/user/users{?remoteId}',
                ],
            ],
            $result,
            'Invalid <usersByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByEmailTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByEmail',
            ],
            $result,
            'Missing <usersByEmail> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByEmailTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByEmail',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                    'href' => '/api/ibexa/v2/user/users{?email}',
                ],
            ],
            $result,
            'Invalid <usersByEmail> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByLoginTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByLogin',
            ],
            $result,
            'Missing <usersByLogin> element.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUsersByLoginTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'usersByLogin',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                    'href' => '/api/ibexa/v2/user/users{?login}',
                ],
            ],
            $result,
            'Invalid <usersByLogin> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRolesTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'roles',
            ],
            $result,
            'Invalid <contentTypes> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRolesTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'roles',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RoleList+xml',
                    'href' => '/api/ibexa/v2/user/roles',
                ],
            ],
            $result,
            'Invalid <roles> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootLocationTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'rootLocation',
            ],
            $result,
            'Invalid <rootLocation> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootLocationTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'rootLocation',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Location+xml',
                    'href' => '/api/ibexa/v2/content/locations/1/2',
                ],
            ],
            $result,
            'Invalid <rootLocation> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootUserGroupTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'rootUserGroup',
            ],
            $result,
            'Invalid <rootUserGroup> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootUserGroupTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'rootUserGroup',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserGroup+xml',
                    'href' => '/api/ibexa/v2/user/groups/1/5',
                ],
            ],
            $result,
            'Invalid <rootUserGroup> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootMediaFolderTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'rootMediaFolder',
            ],
            $result,
            'Invalid <rootMediaFolder> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRootMediaFolderTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'rootMediaFolder',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Location+xml',
                    'href' => '/api/ibexa/v2/content/locations/1/43',
                ],
            ],
            $result,
            'Invalid <rootMediaFolder> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsLocationByRemoteIdTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'locationByRemoteId',
            ],
            $result,
            'Missing <locationByRemoteId> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsLocationByRemoteIdTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'locationByRemoteId',
                'attributes' => [
                    'media-type' => '',
                    'href' => '/api/ibexa/v2/content/locations{?remoteId}',
                ],
            ],
            $result,
            'Invalid <locationByRemoteId> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsLocationByPathTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'locationByPath',
            ],
            $result,
            'Missing <locationByPath> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsLocationByPathTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'locationByPath',
                'attributes' => [
                    'media-type' => '',
                    'href' => '/api/ibexa/v2/content/locations{?locationPath}',
                ],
            ],
            $result,
            'Invalid <locationByPath> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsTrashTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'trash',
            ],
            $result,
            'Invalid <trash> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsTrashTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'trash',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Trash+xml',
                    'href' => '/api/ibexa/v2/content/trash',
                ],
            ],
            $result,
            'Invalid <trash> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsSectionsTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'sections',
            ],
            $result,
            'Invalid <sections> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsSectionTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'sections',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.SectionList+xml',
                    'href' => '/api/ibexa/v2/content/sections',
                ],
            ],
            $result,
            'Invalid <sections> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsViewsTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'views',
            ],
            $result,
            'Invalid <views> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsViewsTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'views',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.RefList+xml',
                    'href' => '/api/ibexa/v2/views',
                ],
            ],
            $result,
            'Invalid <views> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsObjectStateGroupsTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'objectStateGroups',
            ],
            $result,
            'Missing <objectStateGroups> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsObjectStateGroupsTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'objectStateGroups',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ObjectStateGroupList+xml',
                    'href' => '/api/ibexa/v2/content/objectstategroups',
                ],
            ],
            $result,
            'Invalid <objectStateGroups> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsObjectStatesTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'objectStates',
            ],
            $result,
            'Missing <objectStates> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsObjectStatesTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'objectStates',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ObjectStateList+xml',
                    'href' => '/api/ibexa/v2/content/objectstategroups/{objectStateGroupId}/objectstates',
                ],
            ],
            $result,
            'Invalid <objectStates> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsGlobalUrlAliasesTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'globalUrlAliases',
            ],
            $result,
            'Missing <globalUrlAliases> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsGlobalUrlAliasesTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'globalUrlAliases',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlAliasRefList+xml',
                    'href' => '/api/ibexa/v2/content/urlaliases',
                ],
            ],
            $result,
            'Invalid <globalUrlAliases> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUrlWildcardsTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'urlWildcards',
            ],
            $result,
            'Missing <urlWildcards> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsUrlWildcardsTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'urlWildcards',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UrlWildcardList+xml',
                    'href' => '/api/ibexa/v2/content/urlwildcards',
                ],
            ],
            $result,
            'Invalid <globalUrlAliases> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsCreateSessionTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'createSession',
            ],
            $result,
            'Missing <createSession> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsCreateSessionTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'createSession',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserSession+xml',
                    'href' => '/api/ibexa/v2/user/sessions',
                ],
            ],
            $result,
            'Invalid <createSession> tag attributes.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRefreshSessionTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'refreshSession',
            ],
            $result,
            'Missing <refreshSession> tag.',
            false
        );
    }

    /**
     * @depends testLoadRootResource
     */
    public function testResultContainsRefreshSessionTagAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'refreshSession',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserSession+xml',
                    'href' => '/api/ibexa/v2/user/sessions/{sessionId}/refresh',
                ],
            ],
            $result,
            'Invalid <refreshSession> tag attributes.',
            false
        );
    }

    public function getRandomUriSet(): array
    {
        return [
            ['/api/ibexa/v2/randomUri'],
            ['/api/ibexa/v2/randomUri/level/two'],
            ['/api/ibexa/v2/randomUri/with/arguments?arg=argh'],
        ];
    }
}
