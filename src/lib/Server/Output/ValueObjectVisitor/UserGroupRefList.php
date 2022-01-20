<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

/**
 * UserGroupRefList value object visitor.
 */
class UserGroupRefList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Server\Values\UserGroupRefList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('UserGroupRefList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('UserGroupRefList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $groupCount = count($data->userGroups);

        $generator->startList('UserGroup');
        foreach ($data->userGroups as $userGroup) {
            $generator->startObjectElement('UserGroup');

            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ibexa.rest.load_user_group',
                    [
                        'groupPath' => trim($userGroup->mainLocation->pathString, '/'),
                    ]
                )
            );
            $generator->endAttribute('href');

            if ($data->userId !== null && $groupCount > 1) {
                $generator->startHashElement('unassign');

                $generator->startAttribute(
                    'href',
                    $this->router->generate(
                        'ibexa.rest.unassign_user_from_user_group',
                        [
                            'userId' => $data->userId,
                            'groupPath' => $userGroup->mainLocation->path[count($userGroup->mainLocation->path) - 1],
                        ]
                    )
                );
                $generator->endAttribute('href');

                $generator->startAttribute('method', 'DELETE');
                $generator->endAttribute('method');

                $generator->endHashElement('unassign');
            }

            $generator->endObjectElement('UserGroup');
        }
        $generator->endList('UserGroup');

        $generator->endObjectElement('UserGroupRefList');
    }
}

class_alias(UserGroupRefList::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\UserGroupRefList');
