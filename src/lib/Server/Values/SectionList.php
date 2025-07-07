<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * Section list view model.
 */
class SectionList extends RestValue
{
    /**
     * Sections.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Section[]
     */
    public array $sections;

    /**
     * Path used to load the list of sections.
     */
    public string $path;

    /**
     * Construct.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Section[] $sections
     */
    public function __construct(array $sections, string $path)
    {
        $this->sections = $sections;
        $this->path = $path;
    }
}
