<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[Post(
    uriTemplate: '/content/views',
    name: 'Create View (deprecated)',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Executes a query and returns View including the results. The View input reflects the criteria model of the public PHP API. Deprecated as of eZ Platform 1.0 and will respond 301, use POST /views instead.',
        tags: [
            'Views',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'The View in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The View input in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_MOVED_PERMANENTLY => [
                'description' => 'Moved permanently.',
            ],
            Response::HTTP_BAD_REQUEST => [
                'description' => 'Error - the input does not match the input schema definition.',
            ],
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject(),
        ),
    ),
)]
class ContentViewController extends RestController
{
    /**
     * Creates and executes a content view.
     *
     * @deprecated Since platform 1.0. Forwards the request to the new /views location, but returns a 301.
     *
     * @return \Ibexa\Rest\Server\Values\RestExecutedView
     */
    public function createView()
    {
        $response = $this->forward('ezpublish_rest.controller.views:createView');

        // Add 301 status code and location href
        $response->setStatusCode(Response::HTTP_MOVED_PERMANENTLY);
        $response->headers->set('Location', $this->router->generate('ibexa.rest.views.create'));

        return $response;
    }

    /**
     * @param string $controller
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function forward($controller)
    {
        $path['_controller'] = $controller;
        $subRequest = $this->container->get('request_stack')->getCurrentRequest()->duplicate(null, null, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
