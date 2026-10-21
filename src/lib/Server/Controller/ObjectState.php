<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values\CreatedObjectState;
use Ibexa\Rest\Server\Values\CreatedObjectStateGroup;
use Ibexa\Rest\Server\Values\NoContent;
use Ibexa\Rest\Server\Values\ObjectStateGroupList;
use Ibexa\Rest\Server\Values\ObjectStateList;
use Ibexa\Rest\Values\ContentObjectStates;
use Ibexa\Rest\Values\RestObjectState;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Component\HttpFoundation\Request;

/**
 * ObjectState controller.
 */
class ObjectState extends RestController
{
    /**
     * ObjectState service.
     *
     * @var ObjectStateService
     */
    protected $objectStateService;

    /**
     * Content service.
     *
     * @var ContentService
     */
    protected $contentService;

    /**
     * Construct controller.
     *
     * @param ObjectStateService $objectStateService
     * @param ContentService $contentService
     */
    public function __construct(
        ObjectStateService $objectStateService,
        ContentService $contentService
    ) {
        $this->objectStateService = $objectStateService;
        $this->contentService = $contentService;
    }

    /**
     * Creates a new object state group.
     *
     * @throws ForbiddenException
     *
     * @return CreatedObjectStateGroup
     */
    public function createObjectStateGroup(Request $request)
    {
        try {
            $createdStateGroup = $this->objectStateService->createObjectStateGroup(
                $this->inputDispatcher->parse(
                    new Message(
                        ['Content-Type' => $request->headers->get('Content-Type')],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new CreatedObjectStateGroup(
            [
                'objectStateGroup' => $createdStateGroup,
            ]
        );
    }

    /**
     * Creates a new object state.
     *
     * @param $objectStateGroupId
     *
     * @throws ForbiddenException
     *
     * @return CreatedObjectState
     */
    public function createObjectState(
        $objectStateGroupId,
        Request $request
    ) {
        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);

        try {
            $createdObjectState = $this->objectStateService->createObjectState(
                $objectStateGroup,
                $this->inputDispatcher->parse(
                    new Message(
                        ['Content-Type' => $request->headers->get('Content-Type')],
                        $request->getContent()
                    )
                )
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new CreatedObjectState(
            [
                'objectState' => new RestObjectState(
                    $createdObjectState,
                    $objectStateGroup->id
                ),
            ]
        );
    }

    /**
     * Loads an object state group.
     *
     * @param $objectStateGroupId
     *
     * @return ObjectStateGroup
     */
    public function loadObjectStateGroup($objectStateGroupId)
    {
        return $this->objectStateService->loadObjectStateGroup($objectStateGroupId, Language::ALL);
    }

    /**
     * Loads an object state.
     *
     * @param $objectStateGroupId
     * @param $objectStateId
     *
     * @return RestObjectState
     */
    public function loadObjectState(
        $objectStateGroupId,
        $objectStateId
    ) {
        return new RestObjectState(
            $this->objectStateService->loadObjectState($objectStateId, Language::ALL),
            $objectStateGroupId
        );
    }

    /**
     * Returns a list of all object state groups.
     *
     * @return ObjectStateGroupList
     */
    public function loadObjectStateGroups()
    {
        return new ObjectStateGroupList(
            $this->objectStateService->loadObjectStateGroups(0, -1, Language::ALL)
        );
    }

    /**
     * Returns a list of all object states of the given group.
     *
     * @param $objectStateGroupId
     *
     * @return ObjectStateList
     */
    public function loadObjectStates($objectStateGroupId)
    {
        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);

        return new ObjectStateList(
            $this->objectStateService->loadObjectStates($objectStateGroup, Language::ALL),
            $objectStateGroup->id
        );
    }

    /**
     * The given object state group including the object states is deleted.
     *
     * @param $objectStateGroupId
     *
     * @return NoContent
     */
    public function deleteObjectStateGroup($objectStateGroupId)
    {
        $this->objectStateService->deleteObjectStateGroup(
            $this->objectStateService->loadObjectStateGroup($objectStateGroupId)
        );

        return new NoContent();
    }

    /**
     * The given object state is deleted.
     *
     * @param $objectStateId
     *
     * @return NoContent
     */
    public function deleteObjectState($objectStateId)
    {
        $this->objectStateService->deleteObjectState(
            $this->objectStateService->loadObjectState($objectStateId)
        );

        return new NoContent();
    }

    /**
     * Updates an object state group.
     *
     * @param $objectStateGroupId
     *
     * @throws ForbiddenException
     *
     * @return ObjectStateGroup
     */
    public function updateObjectStateGroup(
        $objectStateGroupId,
        Request $request
    ) {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $objectStateGroup = $this->objectStateService->loadObjectStateGroup($objectStateGroupId);

        try {
            $updatedStateGroup = $this->objectStateService->updateObjectStateGroup($objectStateGroup, $updateStruct);

            return $updatedStateGroup;
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Updates an object state.
     *
     * @param $objectStateGroupId
     * @param $objectStateId
     *
     * @throws ForbiddenException
     *
     * @return RestObjectState
     */
    public function updateObjectState(
        $objectStateGroupId,
        $objectStateId,
        Request $request
    ) {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $objectState = $this->objectStateService->loadObjectState($objectStateId);

        try {
            $updatedObjectState = $this->objectStateService->updateObjectState($objectState, $updateStruct);

            return new RestObjectState($updatedObjectState, $objectStateGroupId);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Returns the object states of content.
     *
     * @param $contentId
     *
     * @return ContentObjectStates
     */
    public function getObjectStatesForContent($contentId)
    {
        $groups = $this->objectStateService->loadObjectStateGroups();
        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $contentObjectStates = [];

        foreach ($groups as $group) {
            try {
                $state = $this->objectStateService->getContentState($contentInfo, $group);
                $contentObjectStates[] = new RestObjectState($state, $group->id);
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        return new ContentObjectStates($contentObjectStates);
    }

    /**
     * Updates object states of content
     * An object state in the input overrides the state of the object state group.
     *
     * @param $contentId
     *
     * @throws ForbiddenException
     *
     * @return ContentObjectStates
     */
    public function setObjectStatesForContent(
        $contentId,
        Request $request
    ) {
        $newObjectStates = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $countByGroups = [];
        foreach ($newObjectStates as $newObjectState) {
            $groupId = (int)$newObjectState->groupId;
            if (array_key_exists($groupId, $countByGroups)) {
                ++$countByGroups[$groupId];
            } else {
                $countByGroups[$groupId] = 1;
            }
        }

        foreach ($countByGroups as $groupId => $count) {
            if ($count > 1) {
                throw new ForbiddenException(
                    /** @Ignore */
                    "Multiple Object states provided for group with ID $groupId"
                );
            }
        }

        $contentInfo = $this->contentService->loadContentInfo($contentId);

        $contentObjectStates = [];
        foreach ($newObjectStates as $newObjectState) {
            $objectStateGroup = $this->objectStateService->loadObjectStateGroup($newObjectState->groupId);
            $this->objectStateService->setContentState($contentInfo, $objectStateGroup, $newObjectState->objectState);
            $contentObjectStates[(int)$objectStateGroup->id] = $newObjectState;
        }

        return new ContentObjectStates($contentObjectStates);
    }
}

class_alias(ObjectState::class, 'EzSystems\EzPlatformRest\Server\Controller\ObjectState');
