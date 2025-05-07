<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Root;
use Ibexa\Rest\Server\Service\ExpressionRouterRootResourceBuilder;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RootTest extends ValueObjectVisitorBaseTest
{
    protected function getRootResourceBuilder(): ExpressionRouterRootResourceBuilder
    {
        $resourceConfig = [
            'Router' => [
                'mediaType' => '',
                'href' => 'router.generate("ibexa.rest.create_content")',
            ],
            'RouterWithAttributes' => [
                'mediaType' => 'UserRefList',
                'href' => 'router.generate("ibexa.rest.load_users")',
            ],
            'TemplateRouter' => [
                'mediaType' => '',
                'href' => 'templateRouter.generate("ibexa.rest.redirect_content", {remoteId: "{remoteId}"})',
            ],
            'TemplateRouterWithAttributes' => [
                'mediaType' => 'UserRefList',
                'href' => 'templateRouter.generate("ibexa.rest.load_users", {roleId: "{roleId}"})',
            ],
        ];

        $this->addRouteExpectation('ibexa.rest.create_content', [], '/content/objects');
        $this->addTemplatedRouteExpectation('ibexa.rest.redirect_content', ['remoteId' => '{remoteId}'], '/content/objects');
        $this->addRouteExpectation('ibexa.rest.load_users', [], '/user/users');
        $this->addTemplatedRouteExpectation('ibexa.rest.load_users', ['roleId' => '{roleId}'], '/user/users{?roleId}');

        $configResolver = $this->createMock(ConfigResolverInterface::class);
        $configResolver
            ->method('getParameter')
            ->with('rest_root_resources')
            ->willReturn($resourceConfig);

        return new ExpressionRouterRootResourceBuilder(
            $this->getRouterMock(),
            $this->getTemplatedRouterMock(),
            $configResolver
        );
    }

    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();
        $rootResourceBuilder = $this->getRootResourceBuilder();

        $generator->startDocument(null);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $rootResourceBuilder->buildRootResource()
        );

        $result = $generator->endDocument(null);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootElement(string $result): void
    {
        $this->assertXMLTag(
            ['tag' => 'Root'],
            $result,
            'Invalid <Root> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Root',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Root+xml',
                ],
            ],
            $result,
            'Invalid <Root> attributes.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRouterTag(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'Router',
            ],
            $result,
            'Invalid <Router> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRouterWithAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'RouterWithAttributes',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                ],
            ],
            $result,
            'Invalid <RouterWithAttributes> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTemplateRouterTag($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'TemplateRouter',
            ],
            $result,
            'Invalid <TemplateRouter> element.',
            false
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsTemplateRouterWithAttributes($result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'TemplateRouterWithAttributes',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.UserRefList+xml',
                ],
            ],
            $result,
            'Invalid <TemplateRouterWithAttributes> element.',
            false
        );
    }

    protected function internalGetVisitor(): Root
    {
        return new Root();
    }
}
