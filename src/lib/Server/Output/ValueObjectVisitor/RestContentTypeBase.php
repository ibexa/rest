<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Core\Repository\Values;

/**
 * Base for RestContentType related value object visitors.
 */
abstract class RestContentTypeBase extends ValueObjectVisitor
{
    /**
     * Returns a suffix for the URL type to generate on basis of the given
     * $contentTypeStatus.
     */
    protected function getUrlTypeSuffix(int $contentTypeStatus): string
    {
        switch ($contentTypeStatus) {
            case Values\ContentType\ContentType::STATUS_DRAFT:
                return '_draft';
            case Values\ContentType\ContentType::STATUS_MODIFIED:
                return '_modified';
            case Values\ContentType\ContentType::STATUS_DEFINED:
            default:
                return '';
        }
    }

    /**
     * Serializes the given $contentTypeStatus to a string representation.
     */
    protected function serializeStatus(int $contentTypeStatus): string
    {
        switch ($contentTypeStatus) {
            case Values\ContentType\ContentType::STATUS_DEFINED:
                return 'DEFINED';

            case Values\ContentType\ContentType::STATUS_DRAFT:
                return 'DRAFT';

            case Values\ContentType\ContentType::STATUS_MODIFIED:
                return 'MODIFIED';
        }

        throw new \RuntimeException("Unknown content type status: '{$contentTypeStatus}'.");
    }
}
