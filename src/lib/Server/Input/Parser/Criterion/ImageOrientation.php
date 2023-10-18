<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image\Orientation as ImageOrientationCriterion;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ImageOrientationCriterionValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImageOrientation extends BaseParser
{
    public const IMAGE_ORIENTATION_CRITERION = 'ImageOrientationCriterion';
    public const FIELD_DEF_IDENTIFIER_KEY = 'fieldDefIdentifier';
    public const ORIENTATION_KEY = 'orientation';

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
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ImageOrientationCriterion
    {
        $this->validateInputArray($data);

        return new ImageOrientationCriterion(
            $data[self::IMAGE_ORIENTATION_CRITERION][self::FIELD_DEF_IDENTIFIER_KEY],
            $data[self::IMAGE_ORIENTATION_CRITERION][self::ORIENTATION_KEY]
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ImageOrientationCriterionValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::IMAGE_ORIENTATION_CRITERION,
                $violations
            );
        }
    }
}
