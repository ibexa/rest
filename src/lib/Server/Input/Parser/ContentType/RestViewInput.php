<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType;

use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Exceptions\ValidationFailedException;
use Ibexa\Rest\Server\Input\Parser\Criterion as CriterionParser;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion\ContentTypeRestViewInputValidatorBuilder;
use Ibexa\Rest\Server\Values\ContentTypeRestViewInput;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RestViewInput extends CriterionParser
{
    public const string VIEW_INPUT_IDENTIFIER = 'ContentTypeQuery';

    public const string IDENTIFIER = 'identifier';

    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeRestViewInput
    {
        $restViewInput = new ContentTypeRestViewInput();
        $restViewInput->languageCode = $data['languageCode'] ?? null;

        $this->validateInputArray($data);

        $queryData = $data[self::VIEW_INPUT_IDENTIFIER];
        $queryMediaType = 'application/vnd.ibexa.api.internal.' . self::VIEW_INPUT_IDENTIFIER;
        $restViewInput->query = $parsingDispatcher->parse($queryData, $queryMediaType);

        return $restViewInput;
    }

    /**
     * @param array<mixed> $data
     */
    private function validateInputArray(array $data): void
    {
        $validatorBuilder = new ContentTypeRestViewInputValidatorBuilder($this->validator);
        $validatorBuilder->validateInputArray($data);
        $violations = $validatorBuilder->build()->getViolations();

        if ($violations->count() > 0) {
            throw new ValidationFailedException(
                self::VIEW_INPUT_IDENTIFIER,
                $violations
            );
        }
    }
}
