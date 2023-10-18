<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion\ImageFileSize;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageFileSizeCriterionValidatorBuilder extends BaseImageCriterionValidatorBuilder
{
    protected function getCollectionConstraints(): array
    {
        return [
            ImageFileSize::IMAGE_FILE_SIZE_CRITERION => new Assert\Collection(
                [
                    ImageFileSize::FIELD_DEF_IDENTIFIER_KEY => $this->getFieldDefIdentifierConstraint(),
                    ImageFileSize::SIZE_KEY => new Assert\Optional(
                        [
                            $this->getRangeConstraint(),
                        ]
                    ),
                ]
            ),
        ];
    }
}
