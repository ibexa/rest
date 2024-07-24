<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Bundle\Rest\Functional;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

class TrashTest extends RESTFunctionalTestCase
{
    /**
     * @return string The created trash item href
     */
    public function testCreateTrashItem()
    {
        return $this->createTrashItem('testCreateTrashItem');
    }

    /**
     * Covers GET /content/trash.
     */
    public function testLoadTrashItems()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ibexa/v2/content/trash')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateTrashItem
     * Covers GET /content/trash/{trashItemId}
     */
    public function testLoadTrashItem($trashItemHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $trashItemHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Covers DELETE /content/trash/{trashItemId}.
     *
     * @depends testCreateTrashItem
     */
    public function testDeleteTrashItem($trashItemId)
    {
        // we create a new one, since restore also needs the feature
        $trashItemHref = $this->createTrashItem($trashItemId);

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $trashItemHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Covers MOVE /content/trash/{trashItemId}.
     *
     * @depends testCreateTrashItem
     */
    public function testRestoreTrashItem($trashItemId)
    {
        self::markTestSkipped('@todo fixme');

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('MOVE', $trashItemId)
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * Covers MOVE /content/trash/{trashItemId} Destination:/content/locations/{locationPath}.
     */
    public function testRestoreTrashItemWithDestination()
    {
        $trashItemHref = $this->createTrashItem('testRestoreTrashItemWithDestination');

        $request = $this->createHttpRequest(
            'MOVE',
            $trashItemHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/content/locations/1/2']
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * Covers DELETE /content/trash.
     */
    public function testEmptyTrash()
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', '/api/ibexa/v2/content/trash')
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Tests that deleting a trashed item will fail.
     */
    public function testDeleteTrashedItemFailsWith404()
    {
        self::markTestSkipped('Makes the DB inconsistent');

        // create a folder
        $folderArray = $this->createFolder('testDeleteTrashedItemFailsWith404', '/api/ibexa/v2/content/locations/1/2');

        // send its main location to trash
        $folderLocations = $this->getContentLocations($folderArray['_href']);
        $this->sendLocationToTrash($folderLocations['LocationList']['Location'][0]['_href']);

        // delete the content we created above
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $folderArray['_href'])
        );

        self::assertHttpResponseCodeEquals($response, 404);
    }

    /**
     * Creates a folder, and sends it to trash.
     *
     * @return string the trashed item href
     */
    private function createTrashItem($id)
    {
        $folder = $this->createFolder($id, '/api/ibexa/v2/content/locations/1/2');
        $folderLocations = $this->getContentLocations($folder['_href']);

        return $this->sendLocationToTrash($folderLocations['LocationList']['Location'][0]['_href']);
    }

    /**
     * @param string $contentHref
     *
     * @return string
     */
    private function sendLocationToTrash(string $contentHref): string
    {
        $trashRequest = $this->createHttpRequest(
            'MOVE',
            $contentHref,
            '',
            '',
            '',
            ['Destination' => '/api/ibexa/v2/content/trash']
        );
        $response = $this->sendHttpRequest($trashRequest);

        self::assertHttpResponseCodeEquals($response, 201);

        $trashHref = $response->getHeader('Location')[0];

        return $trashHref;
    }

    public function testRestoreItemWithDestination(): void
    {
        $trashItemHref = $this->createTrashItem('testItemToRestore');

        $request = $this->createHttpRequest(
            'POST',
            $trashItemHref,
            'RestoreTrashItemInput+json',
            '',
            json_encode(['RestoreTrashItemInput' => ['destination' => '/1/2']], JSON_THROW_ON_ERROR),
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    public function testRestoreTrashItemWithoutDestination(): void
    {
        $trashItemHref = $this->createTrashItem('testItemToRestore');

        $request = $this->createHttpRequest(
            'POST',
            $trashItemHref,
            'RestoreTrashItemInput+json',
            '',
            json_encode(['RestoreTrashItemInput' => []], JSON_THROW_ON_ERROR),
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    public function testRestoreTrashItemWithMissingOriginalLocationThrowsException(): void
    {
        $containerFolder = $this->createFolder('container', '/api/ibexa/v2/content/locations/1/2');
        $containerFolderLocations = $this->getContentLocations($containerFolder['_href']);
        $containerFolderLocationHref = $containerFolderLocations['LocationList']['Location'][0]['_href'];

        $folder = $this->createFolder('toRemove', $containerFolderLocationHref);
        $folderLocations = $this->getContentLocations($folder['_href']);

        // Send folder to trash
        $trashItemHref = $this->sendLocationToTrash($folderLocations['LocationList']['Location'][0]['_href']);

        // Send container folder to trash
        $this->sendLocationToTrash($containerFolderLocationHref);

        $request = $this->createHttpRequest(
            'POST',
            $trashItemHref,
            'RestoreTrashItemInput+json',
            '',
            json_encode(['RestoreTrashItemInput' => []], JSON_THROW_ON_ERROR),
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 403);
    }

    public function testRestoreTrashItemToMissingLocationThrowsForbiddenException(): void
    {
        $trashItemHref = $this->createTrashItem('testItemToRestore');

        $request = $this->createHttpRequest(
            'POST',
            $trashItemHref,
            'RestoreTrashItemInput+json',
            '',
            json_encode(['RestoreTrashItemInput' => ['destination' => '/1/22222']], JSON_THROW_ON_ERROR),
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 403);
    }
}
