<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion\ImageMimeType;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageMimeTypeCriterionValidatorBuilder extends BaseImageCriterionValidatorBuilder
{
    protected function getCollectionConstraints(): array
    {
        return [
            ImageMimeType::IMAGE_MIME_TYPE_CRITERION => new Assert\Collection(
                [
                    ImageMimeType::FIELD_DEF_IDENTIFIER_KEY => $this->getFieldDefIdentifierConstraint(),
                    ImageMimeType::TYPE_KEY => new Assert\Optional(
                        [
                            $this->getStringOrArrayOfStringConstraint(),
                        ]
                    ),
                ]
            ),
        ];
    }
}
