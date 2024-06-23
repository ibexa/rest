<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser;

use Ibexa\Rest\Server\Input\Parser\RestoreTrashItemInput;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

final class RestoreTrashItemInputValidatorBuilder extends BaseInputParserValidatorBuilder
{
    protected function buildConstraint(): Constraint
    {
        return new Assert\AtLeastOneOf(
            [
                new Assert\Count(0),
                new Assert\Collection(
                    [
                        RestoreTrashItemInput::DESTINATION_KEY => [
                            new Assert\NotBlank(),
                            new Assert\Type('string'),
                            new Assert\Regex('/^(\/\d+)+$/'),
                        ],
                    ],
                ),
            ],
        );
    }
}
