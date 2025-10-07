<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\QueryBuilder;

use Ibexa\Contracts\Core\Repository\Values\ContentType\Query\ContentTypeQuery;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class GetBasedQueryContentTypeQueryBuilder implements ContentTypeQueryBuilderInterface
{
    private const CONTENT_TYPE_QUERY_MEDIA_TYPE = 'application/vnd.ibexa.api.internal.ContentTypeQuery';

    private ParsingDispatcher $parsingDispatcher;

    public function __construct(ParsingDispatcher $parsingDispatcher)
    {
        $this->parsingDispatcher = $parsingDispatcher;
    }

    public function buildQuery(Request $request, int $defaultLimit): ContentTypeQuery
    {
        $limit = (int)($request->get('limit') ?? $defaultLimit);
        $offset = (int)($request->get('offset') ?? 0);
        $filter = $request->get('filter');
        $sort = $request->get('sort');

        return $this->parsingDispatcher->parse(
            [
                'Filter' => $filter,
                'SortClauses' => $sort ?? [],
                'limit' => $limit,
                'offset' => $offset,
            ],
            self::CONTENT_TYPE_QUERY_MEDIA_TYPE
        );
    }
}
