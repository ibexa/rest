<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\Repository\Values\User\User;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Rest\Server\Values;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;

class UserSessionTest extends ValueObjectVisitorBaseTest
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
            false
        );

        $this->getVisitorMock()->expects($this->at(0))
            ->method('setStatus')
            ->with($this->equalTo(200));

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

    /**
     * Test if result contains Session element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSessionElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Session',
                'children' => [
                    'count' => 4,
                ],
            ],
            $result,
            'Invalid <Session> element.',
            false
        );
    }

    /**
     * Test if result contains Session element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSessionAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'Session',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.Session+xml',
                    'href' => '/user/sessions/sessionId',
                ],
            ],
            $result,
            'Invalid <Session> attributes.',
            false
        );
    }

    /**
     * Test if result contains name value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsNameValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'name',
                'content' => 'sessionName',
            ],
            $result,
            'Invalid or non-existing <Session> name value element.',
            false
        );
    }

    /**
     * Test if result contains identifier value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdentifierValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'identifier',
                'content' => 'sessionId',
            ],
            $result,
            'Invalid or non-existing <Session> identifier value element.',
            false
        );
    }

    /**
     * Test if result contains csrf-token value element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsCsrfTokenValueElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'csrfToken',
                'content' => 'csrfToken',
            ],
            $result,
            'Invalid or non-existing <Session> csrf-token value element.',
            false
        );
    }

    protected function getUserMock()
    {
        $user = $this->createMock(User::class);
        $user->expects($this->any())
            ->method('__get')
            ->with($this->equalTo('id'))
            ->willReturn('user123');

        return $user;
    }

    /**
     * Test if result contains User element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserElement($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'User',
            ],
            $result,
            'Invalid <User> element.',
            false
        );
    }

    /**
     * Test if result contains User element attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsUserAttributes($result)
    {
        $this->assertXMLTag(
            [
                'tag' => 'User',
                'attributes' => [
                    'href' => '/user/users/user123',
                    'media-type' => 'application/vnd.ibexa.api.User+xml',
                ],
            ],
            $result,
            'Invalid <User> element attributes.',
            false
        );
    }

    /**
     * Get the Session visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\UserSession
     */
    protected function internalGetVisitor()
    {
        return new ValueObjectVisitor\UserSession();
    }
}

class_alias(UserSessionTest::class, 'EzSystems\EzPlatformRest\Tests\Server\Output\ValueObjectVisitor\UserSessionTest');
