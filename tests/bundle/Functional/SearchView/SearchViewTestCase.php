<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Bundle\Rest\Functional\SearchView;

use Ibexa\Tests\Bundle\Rest\Functional\TestCase as RESTFunctionalTestCase;

/**
 * @internal for internal use by Ibexa REST test framework
 */
abstract class SearchViewTestCase extends RESTFunctionalTestCase
{
    /**
     * Perform search View Query providing payload ($body) in a given $format.
     *
     * @param string $format xml or json
     */
    protected function getQueryResultsCount(string $format, string $body): int
    {
        $request = $this->createHttpRequest(
            'POST',
            '/api/ibexa/v2/views',
            "ViewInput+{$format}; version=1.1",
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
        $jsonResponse = json_decode($response->getBody()->getContents());

        if (isset($jsonResponse->ErrorMessage)) {
            self::fail(var_export($jsonResponse, true));
        }

        return $jsonResponse->View->Result->count;
    }
}

class_alias(SearchViewTestCase::class, 'EzSystems\EzPlatformRestBundle\Tests\Functional\SearchView\SearchViewTestCase');
