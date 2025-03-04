<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Factory\OpenApiFactory;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Post(
    uriTemplate: '/user/token/jwt',
    name: 'Create JWT token',
    extraProperties: [OpenApiFactory::OVERRIDE_OPENAPI_RESPONSES => false],
    openapi: new Model\Operation(
        summary: 'Creates JWT authentication token.',
        tags: [
            'User Token',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the token is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
            new Model\Parameter(
                name: 'Content-Type',
                in: 'header',
                required: true,
                description: 'The SessionInput schema encoded in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        requestBody: new Model\RequestBody(
            content: new \ArrayObject([
                'application/vnd.ibexa.api.JWTInput+xml' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/JWTInput',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/token/jwt/POST/JWTInput.xml.example',
                ],
                'application/vnd.ibexa.api.JWTInput+json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/JWTInputWrapper',
                    ],
                    'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/token/jwt/POST/JWTInput.json.example',
                ],
            ]),
        ),
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.JWT+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/JWT',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/token/jwt/POST/JWT.xml.example',
                    ],
                    'application/vnd.ibexa.api.JWT+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/JWTWrapper',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/user/token/jwt/POST/JWT.json.example',
                    ],
                ],
            ],
            Response::HTTP_UNAUTHORIZED => [
                'description' => 'Error - Unauthorized',
            ],
        ],
    ),
)]
final class JWT extends RestController
{
    public function createToken(Request $request): void
    {
        //empty method for Symfony json_login authenticator which is used by Lexik/JWT under the hood
        // for more detail refer to: https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html#symfony-5-3-and-higher
    }
}
