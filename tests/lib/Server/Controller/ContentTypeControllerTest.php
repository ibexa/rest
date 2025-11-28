<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Rest\Server\Controller\ContentType;
use Ibexa\Rest\Server\Values\ContentTypeGroupList;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ContentTypeControllerTest extends TestCase
{
    public function testLoadContentTypeGroupListDefaultsToNonSystem(): void
    {
        $contentTypeService = $this->createMock(ContentTypeService::class);
        $contentTypeGroups = [$this->createMock(ContentTypeGroup::class)];

        $contentTypeService
            ->expects(self::once())
            ->method('loadContentTypeGroups')
            ->with(Language::ALL, false)
            ->willReturn($contentTypeGroups);

        $controller = new ContentType($contentTypeService);

        $result = $controller->loadContentTypeGroupList(new Request());

        self::assertInstanceOf(ContentTypeGroupList::class, $result);
        self::assertSame($contentTypeGroups, $result->contentTypeGroups);
    }

    public function testLoadContentTypeGroupListIncludesSystemWhenRequested(): void
    {
        $contentTypeService = $this->createMock(ContentTypeService::class);
        $contentTypeGroups = [$this->createMock(ContentTypeGroup::class)];

        $contentTypeService
            ->expects(self::once())
            ->method('loadContentTypeGroups')
            ->with(Language::ALL, true)
            ->willReturn($contentTypeGroups);

        $controller = new ContentType($contentTypeService);

        $result = $controller->loadContentTypeGroupList(new Request(['includeSystem' => 'true']));

        self::assertInstanceOf(ContentTypeGroupList::class, $result);
        self::assertSame($contentTypeGroups, $result->contentTypeGroups);
    }
}
