<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Rest\Value as RestValue;

/**
 * ContentType list view model.
 */
class ContentTypeList extends RestValue
{
    /**
     * Content types.
     *
     * @var \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[]
     */
    public array $contentTypes;

    /**
     * Path which was used to fetch the list of content types.
     */
    public string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType[] $contentTypes
     */
    public function __construct(array $contentTypes, string $path)
    {
        $this->contentTypes = $contentTypes;
        $this->path = $path;
    }
}
