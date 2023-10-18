<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Validation\Builder\Input\Parser\BaseInputParserCollectionValidatorBuilder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseImageCriterionValidatorBuilder extends BaseInputParserCollectionValidatorBuilder
{
    protected function getFieldDefIdentifierConstraint(): Constraint
    {
        return new Assert\Required(
            [
                new Assert\Type('string'),
                new Assert\NotBlank(),
            ]
        );
    }

    protected function getStringOrArrayOfStringConstraint(): Constraint
    {
        return new Assert\AtLeastOneOf([
            'constraints' => [
                new Assert\Type('string'),
                new Assert\All(
                    [
                        new Assert\Type('string'),
                        new Assert\NotBlank(),
                    ]
                ),
            ],
        ]);
    }

    protected function getRangeConstraint(): Constraint
    {
        return new Assert\Collection(
            [
                'fields' => [
                    'min' => new Assert\Optional(
                        [
                            new Assert\Type('numeric'),
                        ]
                    ),
                    'max' => new Assert\Optional(
                        [
                            new Assert\Type('numeric'),
                        ]
                    ),
                ],
            ]
        );
    }
}
