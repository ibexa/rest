<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class RoleTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /user/roles.
     *
     * @return string The created role draft href
     */
    public function testCreateRoleWithDraft(): string
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testCreateRoleDraft</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateRoleDraft</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateRoleDraft description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/roles',
            'RoleInput+xml',
            'RoleDraft+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href . '/draft';
    }

    /**
     * Covers GET /user/roles.
     */
    public function testListRoles(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/roles')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishRoleDraft
     * Covers GET /user/roles/{roleId}
     */
    public function testLoadRole(string $roleHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $roleHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishRoleDraft
     * Covers POST /user/roles/{roleId}
     *
     * @return string The created role draft href
     */
    public function testCreateRoleDraft(string $roleHref): string
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testCreateRoleDraft</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testCreateRoleDraft</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testCreateRoleDraft description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            $roleHref,
            'RoleInput+xml',
            'RoleDraft+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href . '/draft';
    }

    /**
     * @depends testCreateRoleDraft
     * Covers GET /user/roles/{roleId}/draft
     */
    public function testLoadRoleDraft(string $roleDraftHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $roleDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishRoleDraft
     * Covers PATCH /user/roles/{roleId}
     */
    public function testUpdateRole(string $roleHref): void
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testUpdateRole</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testUpdateRole</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testUpdateRole description</value>
  </descriptions>
</RoleInput>
XML;

        $request = $this->createHttpRequest('PATCH', $roleHref, 'RoleInput+xml', 'Role+json', $xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateRoleDraft
     * Covers PATCH /user/roles/{roleId}/draft
     */
    public function testUpdateRoleDraft(string $roleDraftHref): void
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>testUpdateRoleDraft</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">testUpdateRoleDraft</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">testUpdateRoleDraft description</value>
  </descriptions>
</RoleInput>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $roleDraftHref,
            'RoleInput+xml',
            'RoleDraft+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers POST /user/roles/{roleId}/policies.
     *
     * @depends testPublishRoleDraft
     *
     * @return string The created policy href
     */
    public function testAddPolicy($roleHref)
    {
        // @todo Error in Resource URL in spec @ https://github.com/ezsystems/ezpublish-kernel/blob/master/doc/specifications/rest/REST-API-V2.rst#151213create-policy
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyCreate>
  <module>content</module>
  <function>create</function>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="2"/>
      </values>
    </limitation>
  </limitations>
</PolicyCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            "$roleHref/policies",
            'PolicyCreate+xml',
            'Policy+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * Covers POST /user/roles/{roleId}/policies.
     *
     * @depends testCreateRoleDraft
     *
     * @return string The created policy href
     */
    public function testAddPolicyByRoleDraft($roleDraftHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyCreate>
  <module>content</module>
  <function>create</function>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="1"/>
      </values>
    </limitation>
  </limitations>
</PolicyCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            $this->roleDraftHrefToRoleHref($roleDraftHref) . '/policies',
            'PolicyCreate+xml',
            'Policy+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * Covers GET /user/roles/{roleId}/policies/{policyId}.
     *
     * @depends testAddPolicy
     */
    public function testLoadPolicy(string $policyHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $policyHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/roles/{roleId}/policies.
     *
     * @depends testPublishRoleDraft
     */
    public function testLoadPolicies($roleHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$roleHref/policies")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers PATCH /user/roles/{roleId}/policies/{policyId}.
     *
     * @depends testAddPolicy
     */
    public function testUpdatePolicy(string $policyHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyUpdate>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="1"/>
      </values>
    </limitation>
  </limitations>
</PolicyUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $policyHref,
            'PolicyUpdate+xml',
            'Policy+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);

        return json_decode($response->getBody(), true)['Policy']['_href'];
    }

    /**
     * Covers PATCH /user/roles/{roleId}/policies/{policyId}.
     *
     * @depends testAddPolicyByRoleDraft
     */
    public function testUpdatePolicyByRoleDraft(string $policyHref): void
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<PolicyUpdate>
  <limitations>
    <limitation identifier="Class">
      <values>
        <ref href="1"/>
      </values>
    </limitation>
  </limitations>
</PolicyUpdate>
XML;

        $request = $this->createHttpRequest('PATCH', $policyHref, 'PolicyUpdate+xml', 'Policy+json', $xml);
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishRoleDraft
     * Covers POST /user/users/{userId}/roles
     *
     * @return string assigned role href
     *
     * @todo stop using the anonymous user, this is dangerous...
     */
    public function testAssignRoleToUser($roleHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleAssignInput>
  <Role href="{$roleHref}" media-type="application/vnd.ibexa.api.RoleAssignInput+xml"/>
</RoleAssignInput>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/users/10/roles',
            'RoleAssignInput+xml',
            'RoleAssignmentList+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        $roleAssignmentArray = json_decode($response->getBody(), true);

        self::assertHttpResponseCodeEquals($response, 200);

        return $roleAssignmentArray['RoleAssignmentList']['RoleAssignment'][0]['_href'];
    }

    /**
     * @covers       \POST /user/users/{userId}/roles
     *
     * @param string $roleHref
     * @param array $limitation
     *
     * @return string assigned role href
     *
     * @dataProvider provideLimitations
     */
    public function testAssignRoleToUserWithLimitation(array $limitation)
    {
        $roleHref = $this->createAndPublishRole('testAssignRoleToUserWithLimitation_' . $limitation['identifier']);

        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleAssignInput>
  <Role href="{$roleHref}" media-type="application/vnd.ibexa.api.RoleAssignInput+xml"/>
  <limitation identifier="{$limitation['identifier']}">
      <values>
          <ref href="{$limitation['href']}" media-type="application/vnd.ibexa.api.{$limitation['identifier']}+xml" />
      </values>
  </limitation>
</RoleAssignInput>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/users/10/roles',
            'RoleAssignInput+xml',
            'RoleAssignmentList+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        $roleAssignmentArray = json_decode($response->getBody(), true);

        self::assertHttpResponseCodeEquals($response, 200);

        return $roleAssignmentArray['RoleAssignmentList']['RoleAssignment'][0]['_href'];
    }

    /**
     * @return array<array<array{identifier: string, href: string}>>
     */
    public function provideLimitations(): array
    {
        return [
            [['identifier' => 'Section', 'href' => '/api/ibexa/v2/content/sections/1']],
            [['identifier' => 'Subtree', 'href' => '/api/ibexa/v2/content/locations/1/2/']],
        ];
    }

    /**
     * Covers GET /user/users/{userId}/roles/{roleId}.
     *
     * @depends testAssignRoleToUser
     */
    public function testLoadRoleAssignmentForUser(string $roleAssignmentHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $roleAssignmentHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE /user/users/{userId}/roles/{roleId}.
     *
     * @depends testAssignRoleToUser
     */
    public function testUnassignRoleFromUser(string $roleAssignmentHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $roleAssignmentHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishRoleDraft
     * Covers POST /user/groups/{groupId}/roles
     *
     * @return string role assignment href
     */
    public function testAssignRoleToUserGroup($roleHref)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleAssignInput>
  <Role href="{$roleHref}" media-type="application/vnd.ibexa.api.RoleAssignInput+xml"/>
  <limitation identifier="Section">
      <values>
          <ref href="/api/ibexa/v2/content/sections/1" media-type="application/vnd.ibexa.api.Section+xml" />
      </values>
  </limitation>
</RoleAssignInput>
XML;
        // Assign to "Guest users" group to avoid affecting other tests
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/groups/1/5/12/roles',
            'RoleAssignInput+xml',
            'RoleAssignmentList+json',
            $xml
        );

        $response = $this->sendHttpRequest($request);
        $roleAssignmentArray = json_decode($response->getBody(), true);

        self::assertHttpResponseCodeEquals($response, 200);

        return $roleAssignmentArray['RoleAssignmentList']['RoleAssignment'][0]['_href'];
    }

    /**
     * Covers GET /user/groups/{groupId}/roles/{roleId}.
     *
     * @depends testAssignRoleToUserGroup
     */
    public function testLoadRoleAssignmentForUserGroup(string $roleAssignmentHref): void
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', $roleAssignmentHref)
        );

        self::markTestIncomplete('Requires that visitors are fixed (group url generation)');
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE /user/groups/{groupId}/roles/{roleId}.
     *
     * @depends testAssignRoleToUserGroup
     */
    public function testUnassignRoleFromUserGroup(string $roleAssignmentHref): void
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('DELETE', $roleAssignmentHref)
        );

        self::markTestIncomplete('Requires that visitors are fixed (group url generation)');
        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/users/{userId}/roles.
     */
    public function testLoadRoleAssignmentsForUser(): void
    {
        $response = $this->sendHttpRequest(
            $request = $this->createHttpRequest('GET', '/api/ibexa/v2/user/users/10/roles')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/groups/{groupPath}/roles.
     */
    public function testLoadRoleAssignmentsForUserGroup(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/groups/1/5/44/roles')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers GET /user/policies?userId={userId}.
     */
    public function testListPoliciesForUser(): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/user/policies?userId=10')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE /user/roles/{roleId}/policies/{policyId}.
     *
     * @depends testUpdatePolicy
     */
    public function testDeletePolicy(string $policyHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $policyHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /user/roles/{roleId}/policies/{policyId}.
     *
     * @depends testAddPolicyByRoleDraft
     */
    public function testRemovePolicyByRoleDraft(string $policyHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $policyHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /user/roles/{roleId}/policies.
     *
     * @depends testPublishRoleDraft
     */
    public function testDeletePolicies($roleHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "$roleHref/policies")
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers DELETE /user/roles/{roleId}.
     *
     * @depends testPublishRoleDraft
     */
    public function testDeleteRole(string $roleHref): void
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $roleHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers PUBLISH /user/roles/{roleId}/draft.
     *
     * @depends testCreateRoleDraft
     */
    public function testPublishRoleDraft(string $roleDraftHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', $roleDraftHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
        self::assertHttpResponseHasHeader(
            $response,
            'Location',
            '/api/ibexa/v2/user/roles/' . preg_replace('/.*roles\/(\d+).*/', '$1', $roleDraftHref)
        );

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * Covers DELETE /user/roles/{roleId}/draft.
     *
     * @depends testCreateRoleDraft
     */
    public function testDeleteRoleDraft($roleDraftHref): void
    {
        // we need to create a role draft first since we published the previous one in testPublishRoleDraft
        $roleHref = $this->testCreateRoleDraft($this->roleDraftHrefToRoleHref($roleDraftHref));

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $roleHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Helper method for changing a roledraft href to a role href.
     *
     * @param string $roleDraftHref Role draft href
     *
     * @return string Role href
     */
    private function roleDraftHrefToRoleHref($roleDraftHref): string
    {
        return str_replace('/draft', '', $roleDraftHref);
    }

    /**
     * Creates and publishes a role with $identifier.
     *
     * @param string $identifier
     *
     * @return string The href of the published role
     */
    private function createAndPublishRole(string $identifier)
    {
        $xml = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RoleInput>
  <identifier>$identifier</identifier>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <names>
    <value languageCode="eng-GB">$identifier</value>
  </names>
  <descriptions>
    <value languageCode="eng-GB">$identifier description</value>
  </descriptions>
</RoleInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/user/roles',
            'RoleInput+xml',
            'RoleDraft+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
        $href = $response->getHeader('Location')[0];

        $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', $href . '/draft')
        );

        $this->addCreatedElement($href);

        return $href;
    }
}
