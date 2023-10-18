<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion\ImageOrientation;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageOrientationCriterionValidatorBuilder extends BaseImageCriterionValidatorBuilder
{
    protected function getCollectionConstraints(): array
    {
        return [
            ImageOrientation::IMAGE_ORIENTATION_CRITERION => new Assert\Collection(
                [
                    ImageOrientation::FIELD_DEF_IDENTIFIER_KEY => $this->getFieldDefIdentifierConstraint(),
                    ImageOrientation::ORIENTATION_KEY => new Assert\Optional(
                        [
                            $this->getStringOrArrayOfStringConstraint(),
                        ]
                    ),
                ]
            ),
        ];
    }
}
