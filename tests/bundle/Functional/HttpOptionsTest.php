<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional;

/**
 * Test sending OPTIONS header for REST routes.
 */
class HttpOptionsTest extends TestCase
{
    /**
     * Covers OPTIONS on selected routes.
     *
     * @dataProvider providerForTestHttpOptions
     *
     * @param array<string> $expectedMethods
     */
    public function testHttpOptions(
        string $route,
        array $expectedMethods,
        ?string $contentType = null
    ): void {
        $restAPIPrefix = '/api/ibexa/v2';

        $response = $this->sendHttpRequest(
            $this->createHttpRequest(
                'OPTIONS',
                "{$restAPIPrefix}{$route}",
                $contentType ?? '',
            )
        );

        self::assertHttpResponseCodeEquals($response, 200);
        self::assertEquals(0, (int)($response->getHeader('Content-Length')[0]));

        self::assertHttpResponseHasHeader($response, 'Allow');
        $actualMethods = explode(',', $response->getHeader('Allow')[0]);
        self::assertEqualsCanonicalizing($expectedMethods, $actualMethods);
    }

    /**
     * Data provider for testHttpOptions.
     *
     * @see testHttpOptions
     *
     * @return array<array{non-empty-string, array<non-empty-string>}> Data Provider sets
     */
    public function providerForTestHttpOptions(): array
    {
        return [
            ['/', ['GET']],
            ['/content/sections', ['GET', 'POST']],
            ['/content/sections/1', ['GET', 'PATCH', 'DELETE']],
            ['/content/objects', ['GET', 'POST']],
            ['/content/objects/1', ['POST'], 'CopyContentInput+json'],
            ['/content/objects/1', ['PATCH', 'GET', 'DELETE', 'COPY']],
            ['/content/objects/1/translations/eng-GB', ['DELETE']],
            ['/content/objects/1/relations', ['GET']],
            ['/content/objects/1/versions', ['GET']],
            ['/content/objects/1/versions/1/relations', ['GET', 'POST']],
            ['/content/objects/1/versions/1/relations/1', ['GET', 'DELETE']],
            ['/content/objects/1/versions/1', ['POST'], 'CreateDraftFromVersionInput+json'],
            ['/content/objects/1/versions/1', ['GET', 'PATCH', 'DELETE', 'COPY', 'PUBLISH']],
            ['/content/objects/1/versions/1', ['POST'], 'PublishContentVersionInput+json'],
            ['/content/objects/1/versions/1/translations/eng-GB', ['DELETE']],
            ['/content/objects/1/currentversion', ['POST'], 'CreateDraftFromCurrentVersionInput+json'],
            ['/content/objects/1/currentversion', ['GET', 'COPY']],
            ['/content/binary/images/1-2-3/variations/123', ['GET']],
            ['/views', ['POST', 'GET']],
            ['/views/1', ['GET']],
            ['/views/1/results', ['GET']],
            ['/content/objectstategroups', ['GET', 'POST']],
            ['/content/objectstategroups/1', ['GET', 'PATCH', 'DELETE']],
            ['/content/objectstategroups/1/objectstates', ['GET', 'POST']],
            ['/content/objectstategroups/1/objectstates/1', ['GET', 'PATCH', 'DELETE']],
            ['/content/objects/1/objectstates', ['GET', 'PATCH']],
            ['/content/locations', ['GET']],
            ['/content/locations/1/2', ['POST'], 'CopyLocationInput+json'],
            ['/content/locations/1/2', ['POST'], 'MoveLocationInput+json'],
            ['/content/locations/1/2', ['POST'], 'SwapLocationInput+json'],
            ['/content/locations/1/2', ['GET', 'PATCH', 'DELETE', 'COPY', 'MOVE', 'SWAP']],
            ['/content/locations/1/2', ['POST', 'TRASH'], 'TrashLocationInput+json'],
            ['/content/locations/1/2/children', ['GET']],
            ['/content/objects/1/locations', ['GET', 'POST']],
            ['/content/typegroups', ['GET', 'POST']],
            ['/content/typegroups/1', ['GET', 'PATCH', 'DELETE']],
            ['/content/typegroups/1/types', ['GET', 'POST']],
            ['/content/types', ['GET']],
            ['/content/types/1', ['POST'], 'CopyContentTypeInput+json'],
            ['/content/types/1', ['COPY', 'GET', 'POST', 'DELETE']],
            ['/content/types/1/draft', ['POST'], 'PublishContentTypeInput+json'],
            ['/content/types/1/draft', ['DELETE', 'GET', 'PATCH', 'PUBLISH']],
            ['/content/types/1/fieldDefinitions', ['GET']],
            ['/content/types/1/fieldDefinitions/1', ['GET']],
            ['/content/types/1/draft/fieldDefinitions', ['GET', 'POST']],
            ['/content/types/1/draft/fieldDefinitions/1', ['GET', 'PATCH', 'DELETE']],
            ['/content/types/1/groups', ['GET', 'POST']],
            ['/content/types/1/groups/1', ['DELETE']],
            ['/content/trash', ['GET', 'DELETE']],
            ['/content/trash/1', ['GET', 'DELETE', 'MOVE']],
            ['/content/trash/1', ['POST'], 'RestoreTrashItemInput+json'],
            ['/content/urlwildcards', ['GET', 'POST']],
            ['/content/urlwildcards/1', ['GET', 'DELETE']],
            ['/user/policies', ['GET']],
            ['/user/roles', ['GET', 'POST']],
            ['/user/roles/1', ['POST', 'GET', 'PATCH', 'DELETE']],
            ['/user/roles/1/draft', ['GET', 'PATCH', 'PUBLISH', 'DELETE']],
            ['/user/roles/1/draft', ['POST'], 'PublishRoleInput+json'],
            ['/user/roles/1/policies', ['GET', 'POST', 'DELETE']],
            ['/user/roles/1/policies/328', ['GET', 'PATCH', 'DELETE']],
            ['/user/current', ['GET']],
            ['/user/users', ['HEAD', 'GET']],
            ['/user/users/10', ['GET', 'PATCH', 'DELETE']],
            ['/user/users/10/groups', ['GET', 'POST']],
            ['/user/users/10/groups/4', ['DELETE']],
            ['/user/users/10/drafts', ['GET']],
            ['/user/users/10/roles', ['GET', 'POST']],
            ['/user/users/10/roles/1', ['GET', 'DELETE']],
            ['/user/groups', ['GET']],
            ['/user/groups/root', ['GET']],
            ['/user/groups/subgroups', ['POST']],
            ['/user/groups/4', ['GET', 'PATCH', 'DELETE', 'MOVE']],
            ['/user/groups/4', ['POST'], 'MoveUserGroupInput+json'],
            ['/user/groups/4/subgroups', ['GET', 'POST']],
            ['/user/groups/4/users', ['GET', 'POST']],
            ['/user/groups/4/roles', ['GET', 'POST']],
            ['/user/groups/13/roles/1', ['GET', 'DELETE']],
            ['/user/sessions', ['POST']],
            ['/user/sessions/sess_123', ['DELETE']],
            ['/user/sessions/sess_123/refresh', ['POST']],
            ['/content/urlaliases', ['GET', 'POST']],
            ['/content/locations/1/2/urlaliases', ['GET']],
            ['/content/urlaliases/12-7ade12329f398e8e9ed984f2db6be2c4', ['GET', 'DELETE']],
            ['/services/countries', ['GET']],
        ];
    }
}
