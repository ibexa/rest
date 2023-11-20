<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion\Image;
use Ibexa\Rest\Server\Input\Parser\Criterion\ImageDimensions;
use Ibexa\Rest\Server\Input\Parser\Criterion\ImageFileSize;
use Ibexa\Rest\Server\Input\Parser\Criterion\ImageOrientation;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageCriterionValidatorBuilder extends BaseImageCriterionValidatorBuilder
{
    protected function getCollectionConstraints(): array
    {
        return [
            Image::IMAGE_CRITERION => new Assert\Collection(
                [
                    Image::FIELD_DEF_IDENTIFIER_KEY => $this->getFieldDefIdentifierConstraint(),
                    Image::MIME_TYPES_KEY => new Assert\Optional(
                        [
                            $this->getStringOrArrayOfStringConstraint(),
                        ]
                    ),
                    ImageFileSize::SIZE_KEY => new Assert\Optional(
                        [
                            $this->getRangeConstraint(),
                        ]
                    ),
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
