<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Image\MimeType as ImageMimeTypeCriterion;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ImageMimeTypeCriterionValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ImageMimeType extends BaseParser
{
    public const IMAGE_MIME_TYPE_CRITERION = 'ImageMimeTypeCriterion';
    public const FIELD_DEF_IDENTIFIER_KEY = 'fieldDefIdentifier';
    public const TYPE_KEY = 'type';

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
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ImageMimeTypeCriterion
    {
        $this->validateInputArray($data);

        return new ImageMimeTypeCriterion(
            $data[self::IMAGE_MIME_TYPE_CRITERION][self::FIELD_DEF_IDENTIFIER_KEY],
            $data[self::IMAGE_MIME_TYPE_CRITERION][self::TYPE_KEY] ?? [],
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ImageMimeTypeCriterionValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::IMAGE_MIME_TYPE_CRITERION,
                $violations
            );
        }
    }
}
