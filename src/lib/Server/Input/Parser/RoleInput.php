<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\RoleService;
use Ibexa\Contracts\Core\Repository\Values\User\RoleCreateStruct;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;

/**
 * Parser for RoleInput.
 */
class RoleInput extends BaseParser
{
    /**
     * Role service.
     */
    protected RoleService $roleService;

    protected ParserTools $parserTools;

    /**
     * Construct.
     *
     * @param \Ibexa\Contracts\Core\Repository\RoleService $roleService
     * @param \Ibexa\Rest\Input\ParserTools $parserTools
     */
    public function __construct(RoleService $roleService, ParserTools $parserTools)
    {
        $this->roleService = $roleService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): RoleCreateStruct
    {
        // Since RoleInput is used both for creating and updating role and identifier is not
        // required when updating role, we need to rely on PAPI to throw the exception on missing
        // identifier when creating a role
        // @todo Bring in line with XSD which says that identifier is required always

        $roleIdentifier = null;
        if (array_key_exists('identifier', $data)) {
            $roleIdentifier = $data['identifier'];
        }

        $roleCreateStruct = $this->roleService->newRoleCreateStruct($roleIdentifier);

        return $roleCreateStruct;
    }
}
