<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\PolicyUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for PolicyUpdate.
 */
class PolicyUpdate extends BaseParser
{
    protected RoleService $roleService;

    protected ParserTools $parserTools;

    public function __construct(RoleService $roleService, ParserTools $parserTools)
    {
        $this->roleService = $roleService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): PolicyUpdateStruct
    {
        $policyUpdate = $this->roleService->newPolicyUpdateStruct();

        // @todo XSD says that limitations field is mandatory, but
        // it needs to be possible to remove limitations from policy
        if (array_key_exists('limitations', $data)) {
            if (!is_array($data['limitations'])) {
                throw new Exceptions\Parser("Invalid format for 'limitations' in PolicyUpdate.");
            }

            if (!isset($data['limitations']['limitation']) || !is_array($data['limitations']['limitation'])) {
                throw new Exceptions\Parser("Invalid format for 'limitations' in PolicyUpdate.");
            }

            foreach ($data['limitations']['limitation'] as $limitationData) {
                $policyUpdate->addLimitation(
                    $this->parserTools->parseLimitation($limitationData)
                );
            }
        }

        return $policyUpdate;
    }
}
