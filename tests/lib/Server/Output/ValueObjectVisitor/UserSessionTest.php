<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\UserSession;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTestCase;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;

class UserSessionTest extends ValueObjectVisitorBaseTestCase
{
    /**
     * Test the Session visitor.
     */
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
            false
        );

        $this->getVisitorMock()->expects(self::once())
            ->method('setStatus')
            ->with(self::equalTo(200));

        $setHeaderMatcher = self::exactly(2);
        $this->getVisitorMock()->expects($setHeaderMatcher)
            ->method('setHeader')
            ->willReturnCallback(static function (...$parameters) use ($setHeaderMatcher): void {
                if ($setHeaderMatcher->numberOfInvocations() === 1) {
                    self::assertSame(['Content-Type', 'application/vnd.ibexa.api.Session+xml'], $parameters);
                }
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

        self::assertNotEmpty($result);

        return $result;
    }

    #[Depends('testVisit')]
    public function testResultContainsSessionElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'Session',
                'children' => [
                    'count' => 4,
                ],
            ],
            $result,
            'Invalid <Session> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsSessionAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'Session',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Session+xml',
                    'href' => '/user/sessions/sessionId',
                ],
            ],
            $result,
            'Invalid <Session> attributes.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsNameValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'name',
                'content' => 'sessionName',
            ],
            $result,
            'Invalid or non-existing <Session> name value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsIdentifierValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'sessionId',
            ],
            $result,
            'Invalid or non-existing <Session> identifier value element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsCsrfTokenValueElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'csrfToken',
                'content' => 'csrfToken',
            ],
            $result,
            'Invalid or non-existing <Session> csrf-token value element.'
        );
    }

    protected function getUserMock(): User & MockObject
    {
        $user = $this->createMock(User::class);
        $user->expects(self::any())
            ->method('__get')
            ->with(self::equalTo('id'))
            ->willReturn('user123');

        return $user;
    }

    #[Depends('testVisit')]
    public function testResultContainsUserElement(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'User',
            ],
            $result,
            'Invalid <User> element.'
        );
    }

    #[Depends('testVisit')]
    public function testResultContainsUserAttributes(string $result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'User',
                'attributes' => [
                    'href' => '/user/users/user123',
                    'media-type' => 'application/vnd.ibexa.api.User+xml',
                ],
            ],
            $result,
            'Invalid <User> element attributes.'
        );
    }

    protected function internalGetVisitor(): UserSession
    {
        return new UserSession();
    }
}
