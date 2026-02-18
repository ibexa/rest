<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\QueryBuilder;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\ContentTypeQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
interface ContentTypeQueryBuilderInterface
{
    public function buildQuery(Request $request, int $defaultLimit): ContentTypeQuery;
}
