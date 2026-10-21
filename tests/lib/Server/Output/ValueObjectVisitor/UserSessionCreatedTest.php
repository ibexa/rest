<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Values;

class UserSessionCreatedTest extends UserSessionTest
{
    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $session = new Values\UserSession(
            $this->getUserMock(),
            'sessionName',
            'sessionId',
            'csrfToken',
            true
        );

        $calls = [];
        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(201))
            ->willReturnCallback(static function () use (&$calls): void {
                $calls[] = 'setStatus';
            });

        $setHeaderMatcher = self::exactly(2);
        $this->getVisitorMock()->expects($setHeaderMatcher)
            ->method('setHeader')
            ->willReturnCallback(static function (...$parameters) use ($setHeaderMatcher, &$calls): void {
                if ($setHeaderMatcher->getInvocationCount() === 1) {
                    self::assertSame(['Content-Type', 'application/vnd.ibexa.api.Session+xml'], $parameters);
                } else {
                    self::assertSame(['Accept-Patch', false], $parameters);
                }
                $calls[] = 'setHeader ' . $parameters[0];
            });

        $this->addRouteExpectation(
            'ibexa.rest.delete_session',
            [
                'sessionId' => $session->sessionId,
            ],
            "/user/sessions/{$session->sessionId}"
        );

        $this->addRouteExpectation(
            'ibexa.rest.load_user',
            ['userId' => $session->user->id],
            "/user/users/{$session->user->id}"
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $session
        );

        $result = $generator->endDocument(null);

        self::assertSame(['setStatus', 'setHeader Content-Type', 'setHeader Accept-Patch'], $calls);
        self::assertNotEmpty($result);

        return $result;
    }
}
