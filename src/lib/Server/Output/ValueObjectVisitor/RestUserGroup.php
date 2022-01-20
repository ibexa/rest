<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Rest\Server\Values\Version as VersionValue;

/**
 * RestUserGroup value object visitor.
 */
class RestUserGroup extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Rest\Server\Values\RestUserGroup $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $contentInfo = $data->contentInfo;
        $mainLocation = $data->mainLocation;
        $mainLocationPath = trim($mainLocation->pathString, '/');

        $generator->startObjectElement('UserGroup');

        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_user_group', ['groupPath' => $mainLocationPath])
        );
        $generator->endAttribute('href');

        $generator->startAttribute('id', $contentInfo->id);
        $generator->endAttribute('id');

        $generator->startAttribute('remoteId', $contentInfo->remoteId);
        $generator->endAttribute('remoteId');

        $visitor->setHeader('Content-Type', $generator->getMediaType('UserGroup'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('UserGroupUpdate'));

        $generator->startObjectElement('ContentType');

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_content_type',
                ['contentTypeId' => $contentInfo->contentTypeId]
            )
        );
        $generator->endAttribute('href');

        $generator->endObjectElement('ContentType');

        $generator->startValueElement('name', $contentInfo->name);
        $generator->endValueElement('name');

        $generator->startObjectElement('Versions', 'VersionList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_content_versions', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Versions');

        $generator->startObjectElement('Section');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_section', ['sectionId' => $contentInfo->sectionId])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Section');

        $generator->startObjectElement('MainLocation', 'Location');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_location',
                ['locationPath' => $mainLocationPath]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('MainLocation');

        $generator->startObjectElement('Locations', 'LocationList');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_locations_for_content', ['contentId' => $contentInfo->id])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Locations');

        $generator->startObjectElement('Owner', 'User');
        $generator->startAttribute(
            'href',
            $this->router->generate('ibexa.rest.load_user', ['userId' => $contentInfo->ownerId])
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Owner');

        $generator->startValueElement('publishDate', $contentInfo->publishedDate->format('c'));
        $generator->endValueElement('publishDate');

        $generator->startValueElement('lastModificationDate', $contentInfo->modificationDate->format('c'));
        $generator->endValueElement('lastModificationDate');

        $generator->startValueElement('mainLanguageCode', $contentInfo->mainLanguageCode);
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $contentInfo->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $visitor->visitValueObject(
            new VersionValue(
                $data->content,
                $data->contentType,
                $data->relations
            )
        );

        $generator->startObjectElement('ParentUserGroup', 'UserGroup');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_user_group',
                [
                    'groupPath' => implode('/', array_slice($mainLocation->path, 0, count($mainLocation->path) - 1)),
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('ParentUserGroup');

        $generator->startObjectElement('Subgroups', 'UserGroupList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_sub_user_groups',
                [
                    'groupPath' => $mainLocationPath,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Subgroups');

        $generator->startObjectElement('Users', 'UserList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_users_from_group',
                [
                    'groupPath' => $mainLocationPath,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Users');

        $generator->startObjectElement('Roles', 'RoleAssignmentList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ibexa.rest.load_role_assignments_for_user_group',
                [
                    'groupPath' => $mainLocationPath,
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Roles');

        $generator->endObjectElement('UserGroup');
    }
}

class_alias(RestUserGroup::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\RestUserGroup');
