<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion\ImageDimensions;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageDimensionsCriterionValidatorBuilder extends BaseImageCriterionValidatorBuilder
{
    protected function getCollectionConstraints(): array
    {
        return [
            ImageDimensions::IMAGE_DIMENSIONS_CRITERION => new Assert\Collection(
                [
                    ImageDimensions::FIELD_DEF_IDENTIFIER_KEY => $this->getFieldDefIdentifierConstraint(),
                    ImageDimensions::WIDTH_KEY => new Assert\Optional(
                        [
                            $this->getRangeConstraint(),
                        ]
                    ),
                    ImageDimensions::HEIGHT_KEY => new Assert\Optional(
                        [
                            $this->getRangeConstraint(),
                        ]
                    ),
                ]
            ),
        ];
    }
}
