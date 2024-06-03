<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Input\Parser\Strategy;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType;
use Ibexa\Rest\Server\Values\ContentTypePostOperationValue;

final readonly class ContentTypePostCopyOperation implements ContentTypePostOperationStrategyInterface
{
    public function __construct(
        private ContentTypeService $contentTypeService
    ) {
    }

    public function supports(ContentTypePostOperationValue $operation): bool
    {
        return $operation->getOperation() === 'copy';
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function execute(ContentType $contentType): ContentType
    {
        return $this->contentTypeService->copyContentType($contentType);
    }
}
