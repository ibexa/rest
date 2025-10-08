<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

use Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\BaseCriterionProcessor;

/**
 * @internal
 *
 * @template TCriterion
 *
 * @phpstan-type TCriterionProcessor \Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\CriterionProcessorInterface<
 *     TCriterion
 * >
 *
 * @extends \Ibexa\Contracts\Rest\Input\Parser\Query\Criterion\BaseCriterionProcessor<
 *     TCriterion
 * >
 */
final class CriterionProcessor extends BaseCriterionProcessor
{
    protected function getMediaTypePrefix(): string
    {
        return 'application/vnd.ibexa.api.internal.criterion.content_type';
    }

    protected function getParserInvalidCriterionMessage(string $criterionName): string
    {
        return "Invalid Criterion id <$criterionName> in <AND>";
    }
}
