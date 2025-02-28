<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller\Content;

use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values;
use Symfony\Component\HttpFoundation\Request;

class ContentCopyController extends RestController
{
    /**
     * Creates a new content object as copy under the given parent location given in the destination header.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copyContent($contentId, Request $request)
    {
        $destination = $request->headers->get('Destination');

        $parentLocationParts = explode('/', $destination);
        $copiedContent = $this->repository->getContentService()->copyContent(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $this->repository->getLocationService()->newLocationCreateStruct(array_pop($parentLocationParts))
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $copiedContent->id]
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function copy(int $contentId, Request $request): Values\ResourceCreated
    {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo($contentId);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $destinationLocation */
        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $copiedContent = $contentService->copyContent(
            $contentInfo,
            $locationService->newLocationCreateStruct($destinationLocation->getId()),
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $copiedContent->id],
            )
        );
    }
}
