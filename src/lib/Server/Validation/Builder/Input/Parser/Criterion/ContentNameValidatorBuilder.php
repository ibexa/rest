<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Validation\Builder\Input\Parser\Criterion;

use Ibexa\Rest\Server\Input\Parser\Criterion\ContentName;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\BaseInputParserCollectionValidatorBuilder;
use Symfony\Component\Validator\Constraints as Assert;

final class ContentNameValidatorBuilder extends BaseInputParserCollectionValidatorBuilder
{
    protected function getCollectionConstraints(): array
    {
        return [
            ContentName::CONTENT_NAME_CRITERION => new Assert\Required(
                [
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                ]
            ),
        ];
    }
}
