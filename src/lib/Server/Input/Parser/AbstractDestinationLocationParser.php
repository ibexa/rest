<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\BaseInputParserValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractDestinationLocationParser extends BaseParser
{
    public const string DESTINATION_KEY = 'destination';

    public function __construct(
        private readonly LocationService $locationService,
        protected readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @phpstan-param array{
     *     'destination': string,
     * } $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): Location
    {
        $this->validateInputData($data);

        return $this->getLocationByPath($data[self::DESTINATION_KEY]);
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    private function getLocationByPath(string $path): Location
    {
        return $this->locationService->loadLocation(
            $this->extractLocationIdFromPath($path)
        );
    }

    private function extractLocationIdFromPath(string $path): int
    {
        $pathParts = explode('/', $path);

        return (int)array_pop($pathParts);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\Parser
     */
    private function validateInputData(array $data): void
    {
        $builder = $this->getValidatorBuilder();
        $builder->validateInputArray($data);
        $errors = $builder->build()->getViolations();
        if ($errors->count() > 0) {
            throw new Exceptions\Parser(
                "The 'destination' element is malformed or missing."
            );
        }
    }

    abstract protected function getValidatorBuilder(): BaseInputParserValidatorBuilder;
}
