<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser\ContentType;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Server\Input\Parser\Criterion as CriterionParser;
use Ibexa\Rest\Server\Values\ContentTypeRestViewInput;

final class RestViewInput extends CriterionParser
{
    private const VIEW_INPUT_IDENTIFIER = 'ContentTypeQuery';

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): ContentTypeRestViewInput
    {
        $restViewInput = new ContentTypeRestViewInput();
        $restViewInput->languageCode = $data['languageCode'] ?? null;

        $viewInputIdentifier = self::VIEW_INPUT_IDENTIFIER;
        if (!array_key_exists($viewInputIdentifier, $data)) {
            throw new Exceptions\Parser('Missing ' . $viewInputIdentifier . ' attribute for <ViewInput>.');
        }

        if (!is_array($data[$viewInputIdentifier])) {
            throw new Exceptions\Parser($viewInputIdentifier . ' attribute for <ViewInput> contains invalid data.');
        }

        $queryData = $data[$viewInputIdentifier];
        $queryMediaType = 'application/vnd.ibexa.api.internal.' . $viewInputIdentifier;
        $restViewInput->query = $parsingDispatcher->parse($queryData, $queryMediaType);

        return $restViewInput;
    }
}
