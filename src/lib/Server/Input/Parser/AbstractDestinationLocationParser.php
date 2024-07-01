<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\BaseInputParserValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

abstract class AbstractDestinationLocationParser extends BaseParser
{
    protected const string PARSER = '';
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
        $lastPart = array_pop($pathParts);

        Assert::integerish($lastPart);

        return (int)$lastPart;
    }

    /**
     * @phpstan-assert array{
     *      'destination': string,
     *  } $data
     *
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ValidationFailedException
     */
    private function validateInputData(array $data): void
    {
        $builder = $this->getValidatorBuilder();
        $builder->validateInputArray($data);
        $violations = $builder->build()->getViolations();
        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                static::PARSER,
                $violations,
            );
        }
    }

    abstract protected function getValidatorBuilder(): BaseInputParserValidatorBuilder;
}
