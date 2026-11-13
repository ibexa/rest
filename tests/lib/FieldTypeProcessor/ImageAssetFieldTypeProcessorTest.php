<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\FieldTypeProcessor;

use Exception;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Base\Exceptions\UnauthorizedException;
use Ibexa\Rest\FieldTypeProcessor\ImageAssetFieldTypeProcessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @covers \Ibexa\Rest\FieldTypeProcessor\ImageAssetFieldTypeProcessor
 */
final class ImageAssetFieldTypeProcessorTest extends TestCase
{
    private const int ASSET_CONTENT_ID = 42;

    /**
     * @dataProvider provideAssetLoadingExceptions
     */
    public function testPostProcessValueHashSkipsVariationsWhenAssetCannotBeLoaded(Exception $exception): void
    {
        $contentService = $this->createMock(ContentService::class);
        $contentService
            ->expects(self::once())
            ->method('loadContent')
            ->with(self::ASSET_CONTENT_ID)
            ->willThrowException($exception);

        $router = $this->createMock(RouterInterface::class);
        $router
            ->expects(self::never())
            ->method('generate');

        $processor = new ImageAssetFieldTypeProcessor(
            $router,
            $contentService,
            ['content_field_identifier' => 'image'],
            ['medium']
        );

        $valueHash = [
            'destinationContentId' => self::ASSET_CONTENT_ID,
            'alternativeText' => null,
        ];

        self::assertSame($valueHash, $processor->postProcessValueHash($valueHash));
    }

    /**
     * @return iterable<string, array{Exception}>
     */
    public static function provideAssetLoadingExceptions(): iterable
    {
        yield 'asset not found' => [
            new NotFoundException('Content', self::ASSET_CONTENT_ID),
        ];

        yield 'asset not readable by the current user' => [
            new UnauthorizedException('content', 'read', ['contentId' => self::ASSET_CONTENT_ID]),
        ];
    }
}
