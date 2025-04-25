<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;

class RelationCreate extends BaseParser
{
    public function parse(array $data, ParsingDispatcher $parsingDispatcher): string
    {
        if (!array_key_exists('Destination', $data) || !is_array($data['Destination'])) {
            throw new Exceptions\Parser("Missing or invalid 'Destination' element for RelationCreate.");
        }

        if (!array_key_exists('_href', $data['Destination'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for the Destination element in RelationCreate.");
        }

        return $this->uriParser->getAttributeFromUri($data['Destination']['_href'], 'contentId');
    }
}
