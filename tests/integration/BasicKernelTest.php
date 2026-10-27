<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Integration\Rest;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Test\Core\IbexaKernelTestCase;
use Ibexa\Rest\Server\Controller\Root as RestRootController;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\Routing\RouterInterface;

#[CoversNothing]
final class BasicKernelTest extends IbexaKernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testBasicKernelCompiles(): void
    {
        $this->getIbexaTestCore()->getServiceByClassName(Repository::class);
        $this->getIbexaTestCore()->getServiceByClassName(RestRootController::class);
        $this->expectNotToPerformAssertions();
    }

    public function testRouterIsAvailable(): void
    {
        $router = self::getContainer()->get('router');
        $router->match('/api/ibexa/v2/');
        $this->expectNotToPerformAssertions();
    }

    public function testTemplatedRouterGeneratesUriTemplates(): void
    {
        $templatedRouter = $this->getIbexaTestCore()->getServiceByClassName(
            RouterInterface::class,
            'ibexa.rest.templated_router'
        );

        self::assertSame(
            '/api/ibexa/v2/user/users{?roleId}',
            $templatedRouter->generate('ibexa.rest.load_users', ['roleId' => '{roleId}'])
        );
        self::assertSame(
            '/api/ibexa/v2/user/sessions/{sessionId}/refresh',
            $templatedRouter->generate('ibexa.rest.refresh_session', ['sessionId' => '{sessionId}'])
        );
    }
}
