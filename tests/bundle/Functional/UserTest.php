<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;
use Psr\Http\Message\ResponseInterface;

final class UserTest extends RESTFunctionalTestCase
{
    private const string HEADER_LOCATION = 'Location';

    /**
     * Covers GET /user/groups/root.
     */
    public function loadRootUserGroup(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/groups/root')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /user/groups/{groupPath}/subgroups.
     *
     * returns the created user group href
     */
    public function testCreateUserGroup(): string
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserGroupCreate>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <remoteId>{$text}</remoteId>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$text}</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>Description of {$text}</fieldValue>
    </field>
  </fields>
</UserGroupCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/groups/1/5/subgroups',
            'UserGroupCreate+xml',
            'UserGroup+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, self::HEADER_LOCATION);

        $href = $response->getHeader(self::HEADER_LOCATION)[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * Covers POST /user/groups/subgroups (user group creation under root subtree).
     */
    public function testCreateRootUserGroup(): void
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserGroupCreate>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <remoteId>{$text}</remoteId>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$text}</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>Description of {$text}</fieldValue>
    </field>
  </fields>
</UserGroupCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/groups/subgroups',
            'UserGroupCreate+xml',
            'UserGroup+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, self::HEADER_LOCATION);

        $href = $response->getHeader(self::HEADER_LOCATION)[0];

        $trimmedHref = str_replace('/api/ibexa/v2/user/groups/', '', $href);
        $parts = explode('/', $trimmedHref);

        self::assertSame('1/5', $parts[0] . '/' . $parts[1]);
    }

    /**
     * Covers GET /user/groups/{groupId}.
     *
     * @depends testCreateUserGroup
     */
    public function testLoadUserGroup(string $groupId): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $groupId)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /user/groups/{groupPath}.
     *
     * @depends testCreateUserGroup
     */
    public function testUpdateUserGroup(string $groupHref): void
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserGroupUpdate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>description</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$text}</fieldValue>
    </field>
  </fields>
</UserGroupUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $groupHref,
            'UserGroupUpdate+xml',
            'UserGroup+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUserGroup
     *
     * Covers POST /user/groups/{groupPath}/users
     *
     * returns created user href
     */
    public function testCreateUser(string $userGroupHref): string
    {
        $text = $this->addTestSuffix(__FUNCTION__);
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserCreate>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <remoteId>{$text}</remoteId>
  <login>{$text}</login>
  <email>{$text}@example.net</email>
  <password>{$text}</password>
  <fields>
    <field>
      <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>John</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>last_name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>Doe</fieldValue>
    </field>
  </fields>
</UserCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            "{$userGroupHref}/users",
            'UserCreate+xml',
            'User+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, self::HEADER_LOCATION);

        $href = $response->getHeader(self::HEADER_LOCATION)[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateUser
     */
    public function testLoadUser(string $userHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $userHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    public function testRedirectToCurrentUser(): void
    {
        $request = $this->createHttpRequest('GET', '/api/ibexa/v2/user/current');

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertHttpResponseHasHeader($response, self::HEADER_LOCATION, '/api/ibexa/v2/user/users/14');
    }

    public function testRedirectToCurrentUserWhenNotLoggedIn(): void
    {
        $request = $this
            ->createHttpRequest('GET', '/api/ibexa/v2/user/current')
            ->withoutHeader('Cookie');

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 401);
        self::assertFalse($response->hasHeader(self::HEADER_LOCATION));
    }

    /**
     * @depends testCreateUser
     *
     * Covers PATCH /user/users/{userId}
     */
    public function testUpdateUser(string $userHref): void
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<UserUpdate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>first_name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>john john</fieldValue>
    </field>
  </fields>
</UserUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $userHref,
            'UserUpdate+xml',
            'User+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users.
     */
    public function testLoadUsers(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/users')
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * @depends testCreateUser
     *
     * Covers GET /user/users?remoteId={userRemoteId}
     */
    public function testLoadUserByRemoteId(): void
    {
        $remoteId = $this->addTestSuffix('testCreateUser');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ibexa/v2/user/users?remoteId=$remoteId")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/groups.
     */
    public function testLoadUserGroups(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/groups')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUserGroup
     *
     * Covers GET /user/groups?remoteId={groupRemoteId}
     */
    public function testLoadUserGroupByRemoteId(): void
    {
        $remoteId = $this->addTestSuffix('testCreateUserGroup');
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "/api/ibexa/v2/user/groups?remoteId=$remoteId")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users/{userId}/drafts.
     *
     * @depends testCreateUser
     */
    public function testLoadUserDrafts(string $userHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$userHref/drafts")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateUserGroup
     *
     * Covers GET /user/groups/{groupPath}/subgroups
     */
    public function testLoadSubUserGroups(string $groupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$groupHref/subgroups")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users/{userId}/groups.
     *
     * @depends testCreateUser
     */
    public function testLoadUserGroupsOfUser(string $userHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$userHref/groups")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/groups/<groupPath>/users.
     *
     * @depends testCreateUserGroup
     */
    public function testLoadUsersFromGroup(string $groupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$groupHref/users")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /user/users/{userId}/groups.
     *
     * @depends testCreateUser
     */
    public function testAssignUserToUserGroup(string $userHref): string
    {
        // /1/5/12 is Members
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('POST', "$userHref/groups?group=/user/groups/1/5/12")
        );

        self::assertHttpResponseCodeEquals($response, 200);

        return $userHref;
    }

    /**
     * Covers DELETE /user/users/{userId}/groups/{groupPath}.
     *
     * @depends testAssignUserToUserGroup
     */
    public function testUnassignUserFromUserGroup(string $userHref): void
    {
        // /1/5/12 is Members
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "$userHref/groups/12")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers MOVE /user/groups/{groupPath}.
     *
     * @depends testCreateUserGroup
     */
    public function testMoveUserGroup(string $groupHref): ResponseInterface
    {
        $request = $this->createHttpRequest(
            'MOVE',
            $groupHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/user/groups/1/5/12']
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);

        return $response;
    }

    /**
     * @depends testMoveUserGroup
     */
    public function testMoveGroup(ResponseInterface $response): ResponseInterface
    {
        $userGroupHref = $response->getHeader('Location')[0];

        $request = $this->createHttpRequest(
            'POST',
            $userGroupHref,
            'MoveUserGroupInput+json',
            '',
            json_encode(
                ['MoveUserGroupInput' => ['destination' => '/1/5']],
                JSON_THROW_ON_ERROR,
            ),
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response;
    }

    /**
     * @depends testMoveGroup
     */
    public function testMoveGroupToMissingLocationThrowsForbiddenException(ResponseInterface $response): void
    {
        $userGroupHref = $response->getHeader('Location')[0];

        $request = $this->createHttpRequest(
            'POST',
            $userGroupHref,
            'MoveUserGroupInput+json',
            '',
            json_encode(
                ['MoveUserGroupInput' => ['destination' => '/1/5/333999']],
                JSON_THROW_ON_ERROR,
            ),
        );

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 403);
    }

    /**
     * @depends testCreateUser
     *
     * Covers POST /user/sessions
     */
    public function testCreateSession(): string
    {
        self::markTestSkipped('@todo fixme');

        $text = $this->addTestSuffix('testCreateUser');
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<SessionInput>
  <login>$text</login>
  <password>$text</password>
</SessionInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/sessions',
            'SessionInput+xml',
            'Session+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, self::HEADER_LOCATION);

        $href = $response->getHeader(self::HEADER_LOCATION)[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateSession
     *
     * Covers DELETE /user/sessions/{sessionId}
     */
    public function testDeleteSession(string $sessionHref): void
    {
        self::markTestSkipped('@todo improve. The session can only be deleted if started !');

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $sessionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateUser
     *
     * Covers DELETE /user/users/{userId}
     */
    public function testDeleteUser(string $userHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $userHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateUserGroup
     *
     * Covers DELETE /user/users/{userId}
     */
    public function testDeleteUserGroup(string $groupHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $groupHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    public function testFilterUsersByLoginQueryParameter(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/users?login=admin')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $response404 = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/users?login=foo')
        );

        self::assertHttpResponseCodeEquals($response404, 404);
    }

    public function testIfLoginIsUsedByAnotherUser(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('HEAD', '/api/ibexa/v2/user/users?login=admin')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $response404 = $this->sendHttpRequest(
            $this->createHttpRequest('HEAD', '/api/ibexa/v2/user/users?login=foo')
        );

        self::assertHttpResponseCodeEquals($response404, 404);
    }

    public function testFilterUsersByEmailQueryParameter(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/users?email=admin@link.invalid')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $response404 = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/users?email=foo@bar.com')
        );

        self::assertHttpResponseCodeEquals($response404, 404);
    }

    public function testIfEmailIsUsedByAnotherUser(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('HEAD', '/api/ibexa/v2/user/users?email=admin@link.invalid')
        );

        self::assertHttpResponseCodeEquals($response, 200);

        $response404 = $this->sendHttpRequest(
            $this->createHttpRequest('HEAD', '/api/ibexa/v2/user/users?email=foo@bar.com')
        );

        self::assertHttpResponseCodeEquals($response404, 404);
    }
}
