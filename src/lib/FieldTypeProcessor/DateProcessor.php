<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

use Ibexa\Contracts\Rest\FieldTypeProcessor;
use Ibexa\Core\FieldType\Date\Type;

class DateProcessor extends FieldTypeProcessor
{
    public function preProcessFieldSettingsHash(mixed $incomingSettingsHash): mixed
    {
        if (isset($incomingSettingsHash['defaultType'])) {
            switch ($incomingSettingsHash['defaultType']) {
                case 'DEFAULT_EMPTY':
                    $incomingSettingsHash['defaultType'] = Type::DEFAULT_EMPTY;
                    break;
                case 'DEFAULT_CURRENT_DATE':
                    $incomingSettingsHash['defaultType'] = Type::DEFAULT_CURRENT_DATE;
            }
        }

        return $incomingSettingsHash;
    }

    public function postProcessFieldSettingsHash(mixed $outgoingSettingsHash): mixed
    {
        if (isset($outgoingSettingsHash['defaultType'])) {
            switch ($outgoingSettingsHash['defaultType']) {
                case Type::DEFAULT_EMPTY:
                    $outgoingSettingsHash['defaultType'] = 'DEFAULT_EMPTY';
                    break;
                case Type::DEFAULT_CURRENT_DATE:
                    $outgoingSettingsHash['defaultType'] = 'DEFAULT_CURRENT_DATE';
            }
        }

        return $outgoingSettingsHash;
    }
}
