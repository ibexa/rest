<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType\Criterion;

final class ContentTypeCriteriaRegistry
{
    /** @var iterable<\Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContentTypeCriterionInterface> */
    private iterable $criteria;

    /**
     * @param iterable<\Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContentTypeCriterionInterface> $criteria
     */
    public function __construct(iterable $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @return iterable<\Ibexa\Rest\Server\Input\Parser\ContentType\Criterion\ContentTypeCriterionInterface>
     */
    public function getCriteria(): iterable
    {
        return $this->criteria;
    }
}
