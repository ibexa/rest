<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/views',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Search content',
        description: 'Executes a query and returns a View including the results.
View input reflects the criteria model of the public PHP API.
Refer to [Search Criteria Reference](/en/latest/search/criteria_reference/search_criteria_reference/)',
        tags: [
            'Views',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'The view in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The view input in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.ViewInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ViewInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/views/POST/ViewInput.xml.example',
                ],
                'application/vnd.ibexa.api.ViewInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ViewInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/views/POST/ViewInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.View+xml; version=1.1' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/View',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/views/POST/View.xml.v11.example',
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
        ],
    ),
)]
/**
 * Controller for Repository Views (Search, mostly).
 */
class Views extends Controller
{
    private SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Creates and executes a content view.
     */
    public function createView(Request $request): Values\RestExecutedView
    {
        /** @var \Ibexa\Rest\Server\Values\RestViewInput $viewInput */
        $viewInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        if ($viewInput->query instanceof LocationQuery) {
            $method = [$this->searchService, 'findLocations'];
        } else {
            $method = [$this->searchService, 'findContent'];
        }

        $languageFilter = [
            'languages' => null !== $viewInput->languageCode ? [$viewInput->languageCode] : Language::ALL,
            'useAlwaysAvailable' => $viewInput->useAlwaysAvailable ?? true,
        ];
        $query = $viewInput->query->query;
        if (!empty($query->value)) {
            $languageFilter['excludeTranslationsFromAlwaysAvailable'] = false;
        }

        return new Values\RestExecutedView(
            [
                'identifier' => $viewInput->identifier,
                'searchResults' => $method(
                    $viewInput->query,
                    $languageFilter
                ),
            ]
        );
    }

    /**
     * List content views.
     */
    public function listView(): NotImplementedException
    {
        return new NotImplementedException('ezpublish_rest.controller.content:listView');
    }

    /**
     * Get a content view.
     */
    public function getView(): NotImplementedException
    {
        return new NotImplementedException('ezpublish_rest.controller.content:getView');
    }

    /**
     * Get a content view results.
     */
    public function loadViewResults(): NotImplementedException
    {
        return new NotImplementedException('ezpublish_rest.controller.content:loadViewResults');
    }
}
