<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\Criterion;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\ContentName as ContentNameCriterion;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ContentNameValidatorBuilder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContentName extends BaseParser
{
    public const CONTENT_NAME_CRITERION = 'ContentNameCriterion';

    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array<mixed> $data
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentNameCriterion
    {
        $this->validateInputArray($data);

        $criterionData = $data[self::CONTENT_NAME_CRITERION];

        return new ContentNameCriterion($criterionData);
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ContentNameValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::CONTENT_NAME_CRITERION,
                $violations
            );
        }
    }
}
