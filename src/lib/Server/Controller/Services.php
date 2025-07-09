<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Model;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\CountryList;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

#[Get(
    uriTemplate: '/services/countries',
    openapi: new Model\Operation(
        summary: 'Countries list',
        description: 'Gives access to an ISO-3166 formatted list of world countries. It is useful when presenting a country options list from any application.',
        tags: [
            'Services',
        ],
        parameters: [
            new Model\Parameter(
                name: 'Accept',
                in: 'header',
                required: true,
                description: 'If set, the country list is returned in XML or JSON format.',
                schema: [
                    'type' => 'string',
                ],
            ),
        ],
        responses: [
            Response::HTTP_OK => [
                'content' => [
                    'application/vnd.ibexa.api.CountriesList+xml' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/CountryList',
                        ],
                        'x-ibexa-example-file' => '@IbexaRestBundle/Resources/api_platform/examples/services/countries/GET/CountriesList.xml.example',
                    ],
                    'application/vnd.ibexa.api.CountriesList+json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/CountryListWrapper',
                        ],
                    ],
                ],
            ],
        ],
    ),
)]
class Services extends RestController
{
    /**
     * @param array<string, array{Name: string, Alpha2: string, Alpha3: string, IDC: string}> $countriesInfo
     */
    public function __construct(
        #[Autowire(param: 'ibexa.field_type.country.data')]
        private readonly array $countriesInfo
    ) {
    }

    public function loadCountryList(): CountryList
    {
        return new CountryList($this->countriesInfo);
    }
}
