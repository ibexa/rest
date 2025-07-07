<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Limitation;

use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Core\Repository\Values;
use Ibexa\Rest\Input\BaseParser;

/**
 * Generic limitation value parser.
 *
 * Instances are built with:
 * - The name of a route parameter, that will be searched for limitation values
 *   Example: "sectionId" from "/content/section/{sectionId}"
 * - The FQN of the limitation value object that the parser builds
 */
class RouteBasedLimitationParser extends BaseParser
{
    /**
     * Name of the route parameter.
     * Example: "sectionId".
     */
    private string $limitationRouteParameterName;

    /**
     * Value object class built by the Parser.
     * Example: "Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation".
     */
    private string $limitationClass;

    public function __construct(string $limitationRouteParameterName, string $limitationClass)
    {
        $this->limitationRouteParameterName = $limitationRouteParameterName;
        $this->limitationClass = $limitationClass;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): Limitation
    {
        if (!array_key_exists('_identifier', $data)) {
            throw new Exceptions\Parser("Missing '_identifier' attribute for Limitation.");
        }

        $limitationObject = $this->buildLimitation();

        if (!isset($data['values']['ref']) || !is_array($data['values']['ref'])) {
            throw new Exceptions\Parser('Invalid format for data values in Limitation.');
        }

        foreach ($data['values']['ref'] as $limitationValue) {
            if (!array_key_exists('_href', $limitationValue)) {
                throw new Exceptions\Parser('Invalid format for data values in Limitation.');
            }

            $limitationObject->limitationValues[] = $this->parseIdFromHref($limitationValue);
        }

        return $limitationObject;
    }

    protected function buildLimitation(): Limitation
    {
        return new $this->limitationClass();
    }

    /**
     * @param array{_href: string} $limitationValue
     */
    protected function parseIdFromHref(array $limitationValue): string
    {
        return $this->uriParser->getAttributeFromUri(
            $limitationValue['_href'],
            $this->limitationRouteParameterName
        );
    }
}
