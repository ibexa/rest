<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\UserSession as UserSessionValue;

/**
 * UserSession value object visitor.
 */
class UserSession extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Rest\Server\Values\UserSession $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $status = $data->created ? 201 : 200;
        $visitor->setStatus($status);

        $visitor->setHeader('Content-Type', $generator->getMediaType('Session'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startObjectElement('Session');
        $this->visitUserSessionAttributes($generator, $data);
        $generator->endObjectElement('Session');
    }

    protected function visitUserSessionAttributes(Generator $generator, UserSessionValue $data): void
    {
        $sessionHref = $this->router->generate('ibexa.rest.delete_session', ['sessionId' => $data->sessionId]);

        $generator->startAttribute('href', $sessionHref);
        $generator->endAttribute('href');

        $generator->startValueElement('name', $data->sessionName);
        $generator->endValueElement('name');

        $generator->startValueElement('identifier', $data->sessionId);
        $generator->endValueElement('identifier');

        $generator->startValueElement('csrfToken', $data->csrfToken);
        $generator->endValueElement('csrfToken');

        $generator->startObjectElement('User', 'User');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_user', ['userId' => $data->user->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('User');
    }
}
