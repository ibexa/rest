<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Input\Parser;

use Ibexa\Rest\Server\Validation\Builder\Input\Parser\BaseInputParserValidatorBuilder;
use Ibexa\Rest\Server\Validation\Builder\Input\Parser\MoveUserGroupInputValidatorBuilder;

final class MoveUserGroupInput extends AbstractDestinationLocationParser
{
    protected const string PARSER = 'MoveUserGroupInput';

    protected function getValidatorBuilder(): BaseInputParserValidatorBuilder
    {
        return new MoveUserGroupInputValidatorBuilder($this->validator);
    }
}
