<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Value as RestValue;

/**
 * Version view model.
 */
class Version extends RestValue
{
    public Content $content;

    public ContentType $contentType;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Relation[]
     */
    public array $relations;

    /**
     * Path used to load this content.
     */
    public ?string $path;

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Relation[] $relations
     */
    public function __construct(Content $content, ContentType $contentType, array $relations, ?string $path = null)
    {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->relations = $relations;
        $this->path = $path;
    }
}
