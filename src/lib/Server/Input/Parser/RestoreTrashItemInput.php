<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\RestoreTrashItemInputValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RestoreTrashItemInput extends BaseParser
{
    public const string DESTINATION_KEY = 'destination';

    public function __construct(
        private readonly LocationService $locationService,
        private readonly ValidatorInterface $validator,
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
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ?Location
    {
        $this->validateInputData($data);

        $location = $data[self::DESTINATION_KEY] ?? null;

        return $location === null ? null : $this->getLocationByPath($location);
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
     * @phpstan-assert array{
     *      destination?: string,
     *  } $data
     *
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ValidationFailedException
     */
    private function validateInputData(array $data): void
    {
        $builder = new RestoreTrashItemInputValidatorBuilder($this->validator);
        $builder->validateInputArray($data);
        $violations = $builder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                'RestoreTrashItemInput',
                $violations,
            );
        }
    }
}
