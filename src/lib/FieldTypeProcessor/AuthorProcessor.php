<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Core\FieldType\Author\Type;

class AuthorProcessor extends FieldTypeProcessor
{
    public function preProcessFieldSettingsHash(mixed $incomingSettingsHash): mixed
    {
        if (isset($incomingSettingsHash['defaultAuthor'])) {
            $incomingSettingsHash['defaultAuthor'] = match ($incomingSettingsHash['defaultAuthor']) {
                'DEFAULT_CURRENT_USER' => Type::DEFAULT_CURRENT_USER,
                default => Type::DEFAULT_VALUE_EMPTY,
            };
        }

        return $incomingSettingsHash;
    }

    public function postProcessFieldSettingsHash(mixed $outgoingSettingsHash): mixed
    {
        if (isset($outgoingSettingsHash['defaultAuthor'])) {
            $outgoingSettingsHash['defaultAuthor'] = match ($outgoingSettingsHash['defaultAuthor']) {
                Type::DEFAULT_CURRENT_USER => 'DEFAULT_CURRENT_USER',
                default => 'DEFAULT_VALUE_EMPTY',
            };
        }

        return $outgoingSettingsHash;
    }
}
