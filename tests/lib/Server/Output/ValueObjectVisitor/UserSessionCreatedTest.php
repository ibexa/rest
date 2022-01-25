<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Rest\Server\Values;

class UserSessionCreatedTest extends UserSessionTest
{
    /**
     * Test the Session visitor.
     *
     * @return string
     */
    public function testVisit()
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

        $this->getVisitorMock()->expects($this->any())
            ->method('setStatus')
            ->with($this->equalTo(201));

        $this->getVisitorMock()->expects($this->at(1))
            ->method('setHeader')
            ->with($this->equalTo('Content-Type'), $this->equalTo('application/vnd.ibexa.api.Session+xml'));

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

        $this->assertNotNull($result);

        return $result;
    }
}

class_alias(UserSessionCreatedTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\UserSessionCreatedTest');
