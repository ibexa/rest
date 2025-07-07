<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\FieldTypeProcessor;

class RelationListProcessor extends BaseRelationProcessor
{
    /**
     * In addition to the list of destinationContentIds, adds a destinationContentHrefs
     * array, with matching content uris.
     */
    public function postProcessValueHash(mixed $outgoingValueHash): array
    {
        if (
            !isset($outgoingValueHash['destinationContentIds']) ||
            !is_array($outgoingValueHash['destinationContentIds']) ||
            !$this->canMapContentHref()
        ) {
            return $outgoingValueHash;
        }

        $outgoingValueHash['destinationContentHrefs'] = array_map(
            function ($contentId): ?string {
                return $this->mapToContentHref($contentId);
            },
            $outgoingValueHash['destinationContentIds']
        );

        return $outgoingValueHash;
    }

    public function postProcessFieldSettingsHash(mixed $outgoingSettingsHash): mixed
    {
        $outgoingSettingsHash = parent::postProcessFieldSettingsHash($outgoingSettingsHash);

        if (!empty($outgoingSettingsHash['selectionDefaultLocation'])) {
            $outgoingSettingsHash['selectionDefaultLocationHref'] = $this->mapToLocationHref(
                $outgoingSettingsHash['selectionDefaultLocation']
            );
        }

        return $outgoingSettingsHash;
    }
}
