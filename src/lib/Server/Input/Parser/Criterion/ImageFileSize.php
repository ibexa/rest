<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image\FileSize as ImageFileSizeCriterion;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ImageFileSizeCriterionValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImageFileSize extends BaseParser
{
    public const IMAGE_FILE_SIZE_CRITERION = 'ImageFileSizeCriterion';
    public const FIELD_DEF_IDENTIFIER_KEY = 'fieldDefIdentifier';
    public const SIZE_KEY = 'size';

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
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ImageFileSizeCriterion
    {
        $this->validateInputArray($data);

        $sizeData = $data[self::IMAGE_FILE_SIZE_CRITERION][self::SIZE_KEY];
        $minFileSize = isset($sizeData['min'])
            ? (int) $sizeData['min']
            : 0;

        $maxFileSize = isset($sizeData['max'])
            ? (int) $sizeData['max']
            : null;

        return new ImageFileSizeCriterion(
            $data[self::IMAGE_FILE_SIZE_CRITERION][self::FIELD_DEF_IDENTIFIER_KEY],
            $minFileSize,
            $maxFileSize,
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ImageFileSizeCriterionValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::IMAGE_FILE_SIZE_CRITERION,
                $violations
            );
        }
    }
}
