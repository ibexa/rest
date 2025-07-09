<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image\Dimensions as ImageDimensionsCriterion;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ImageDimensionsCriterionValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImageDimensions extends BaseParser
{
    public const string IMAGE_DIMENSIONS_CRITERION = 'ImageDimensionsCriterion';
    public const string FIELD_DEF_IDENTIFIER_KEY = 'fieldDefIdentifier';
    public const string WIDTH_KEY = 'width';
    public const string HEIGHT_KEY = 'height';

    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ImageDimensionsCriterion
    {
        $this->validateInputArray($data);

        $criterionData = $data[self::IMAGE_DIMENSIONS_CRITERION];

        return new ImageDimensionsCriterion(
            $criterionData[self::FIELD_DEF_IDENTIFIER_KEY],
            $this->extractImageCriteria($criterionData)
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ImageDimensionsCriterionValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::IMAGE_DIMENSIONS_CRITERION,
                $violations
            );
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return array{
     *     width?: array{min?: int|null, max?: int|null},
     *     height?: array{min?: int|null, max?: int|null},
     * }
     */
    private function extractImageCriteria(array $data): array
    {
        return array_filter(
            $data,
            static fn (string $criteria): bool => self::FIELD_DEF_IDENTIFIER_KEY !== $criteria,
            ARRAY_FILTER_USE_KEY
        );
    }
}
