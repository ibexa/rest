<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\URLWildcard;
use Ibexa\Rest\Value as RestValue;

/**
 * URLWildcard list view model.
 */
class URLWildcardList extends RestValue
{
    /**
     * URL wildcards.
     *
     * @var URLWildcard[]
     */
    public $urlWildcards;

    /**
     * Construct.
     *
     * @param URLWildcard[] $urlWildcards
     */
    public function __construct(array $urlWildcards)
    {
        $this->urlWildcards = $urlWildcards;
    }
}

class_alias(URLWildcardList::class, 'EzSystems\EzPlatformRest\Server\Values\URLWildcardList');
