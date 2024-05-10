<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Service\ExpressionRouterRootResourceBuilder;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class RootTest extends ValueObjectVisitorBaseTest
{
    protected function getRootResourceBuilder()
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

    /**
     * Test the Role visitor.
     *
     * @return string
     */
    public function testVisit()
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

        self::assertNotNull($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsRootElement($result)
    {
        $this->assertXMLTag(
            ['tag' => 'Root'],
            $result,
            'Invalid <Root> element.',
            false
        );
    }

    /**
     * Test if result contains Role element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRootAttributes($result)
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
    public function testResultContainsRouterTag($result)
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
    public function testResultContainsRouterWithAttributes($result)
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
    public function testResultContainsTemplateRouterTag($result)
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
    public function testResultContainsTemplateRouterWithAttributes($result)
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

    /**
     * Get the Role visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Root
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\Root();
    }
}

class_alias(RootTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\RootTest');
