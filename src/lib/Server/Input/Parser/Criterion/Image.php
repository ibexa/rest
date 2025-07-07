<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image as ImageCriterion;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ImageCriterionValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Image extends BaseParser
{
    public const string IMAGE_CRITERION = 'ImageCriterion';
    public const string FIELD_DEF_IDENTIFIER_KEY = 'fieldDefIdentifier';
    public const string MIME_TYPES_KEY = 'mimeTypes';

    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidCriterionArgumentException
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ImageCriterion
    {
        $this->validateInputArray($data);

        $criterionData = $data[self::IMAGE_CRITERION];

        return new ImageCriterion(
            $criterionData[self::FIELD_DEF_IDENTIFIER_KEY],
            $this->extractImageCriteria($criterionData)
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ImageCriterionValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::IMAGE_CRITERION,
                $violations
            );
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return array{
     *      mimeTypes?: string|array<string>,
     *      size?: array{min?: numeric|null, max?: numeric|null},
     *      width?: array{min?: int|null, max?: int|null},
     *      height?: array{min?: int|null, max?: int|null},
     *      orientation?: string|array<string>,
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
