<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
abstract class BaseInputParserValidatorBuilder
{
    private ContextualValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator->startContext();
    }

    abstract protected function buildConstraint(): Constraint;

    /**
     * @phpstan-param array<mixed> $data
     */
    final public function validateInputArray(array $data): self
    {
        $this->validator
            ->validate(
                $data,
                $this->buildConstraint()
            );

        return $this;
    }

    final public function build(): ContextualValidatorInterface
    {
        return $this->validator;
    }
}
