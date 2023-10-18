<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
abstract class BaseInputParserCollectionValidatorBuilder extends BaseInputParserValidatorBuilder
{
    final protected function buildConstraint(): Constraint
    {
        return new Assert\Collection(
            ['fields' => $this->getCollectionConstraints()]
        );
    }

    /**
     * @return array<string, \Symfony\Component\Validator\Constraint>
     */
    abstract protected function getCollectionConstraints(): array;
}
