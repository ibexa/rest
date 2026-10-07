<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Exceptions\BadStateException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeFieldDefinitionValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentTypeValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentType as APIContentType;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroup;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Exceptions\NotFoundException;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\ContentTypeGroupList;
use Ibexa\Rest\Server\Values\ContentTypeGroupRefList;
use Ibexa\Rest\Server\Values\ContentTypeInfoList;
use Ibexa\Rest\Server\Values\ContentTypeList;
use Ibexa\Rest\Server\Values\ContentTypeRestViewInput;
use Ibexa\Rest\Server\Values\CreatedContentType;
use Ibexa\Rest\Server\Values\CreatedContentTypeGroup;
use Ibexa\Rest\Server\Values\CreatedFieldDefinition;
use Ibexa\Rest\Server\Values\FieldDefinitionList;
use Ibexa\Rest\Server\Values\NoContent;
use Ibexa\Rest\Server\Values\ResourceCreated;
use Ibexa\Rest\Server\Values\RestContentType;
use Ibexa\Rest\Server\Values\RestFieldDefinition;
use Symfony\Component\HttpFoundation\Request;

/**
 * ContentType controller.
 */
class ContentType extends RestController
{
    /**
     * Content type service.
     *
     * @var ContentTypeService
     */
    protected $contentTypeService;

    /**
     * Construct controller.
     *
     * @param ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * Creates a new content type group.
     *
     * @throws ForbiddenException
     *
     * @return CreatedContentTypeGroup
     */
    public function createContentTypeGroup(Request $request)
    {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            return new CreatedContentTypeGroup(
                [
                    'contentTypeGroup' => $this->contentTypeService->createContentTypeGroup($createStruct),
                ]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Updates a content type group.
     *
     * @param int|string $contentTypeGroupId
     *
     * @throws ForbiddenException
     *
     * @return ContentTypeGroup
     */
    public function updateContentTypeGroup(
        $contentTypeGroupId,
        Request $request
    ) {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeGroup(
                $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId),
                $this->mapToGroupUpdateStruct($createStruct)
            );

            return $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId, Language::ALL);
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Returns a list of content types of the group.
     *
     * @param int|string $contentTypeGroupId
     *
     * @return ContentTypeList|ContentTypeInfoList
     */
    public function listContentTypesForGroup(
        $contentTypeGroupId,
        Request $request
    ) {
        $contentTypes = $this->contentTypeService->loadContentTypes(
            $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId, Language::ALL),
            Language::ALL
        );

        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.contenttypelist') {
            return new ContentTypeList($contentTypes, $request->getPathInfo());
        }

        return new ContentTypeInfoList($contentTypes, $request->getPathInfo());
    }

    /**
     * The given content type group is deleted.
     *
     * @param mixed $contentTypeGroupId
     *
     * @throws ForbiddenException
     *
     * @return NoContent
     */
    public function deleteContentTypeGroup($contentTypeGroupId)
    {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId);

        $contentTypes = $this->contentTypeService->loadContentTypes($contentTypeGroup);
        if (!empty($contentTypes)) {
            throw new ForbiddenException('Only empty content type groups can be deleted');
        }

        $this->contentTypeService->deleteContentTypeGroup($contentTypeGroup);

        return new NoContent();
    }

    /**
     * Returns a list of all content type groups.
     *
     * @return ContentTypeGroupList
     */
    public function loadContentTypeGroupList(Request $request)
    {
        if ($request->query->has('identifier')) {
            $contentTypeGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier(
                $request->query->get('identifier')
            );

            return new Values\TemporaryRedirect(
                $this->router->generate(
                    'ibexa.rest.load_content_type_group',
                    [
                        'contentTypeGroupId' => $contentTypeGroup->id,
                    ]
                )
            );
        }

        return new ContentTypeGroupList(
            $this->contentTypeService->loadContentTypeGroups(Language::ALL)
        );
    }

    /**
     * Returns the content type group given by id.
     *
     * @param $contentTypeGroupId
     *
     * @return ContentTypeGroup
     */
    public function loadContentTypeGroup($contentTypeGroupId)
    {
        return $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId, Language::ALL);
    }

    /**
     * Loads a content type.
     *
     * @param $contentTypeId
     *
     * @return RestContentType
     */
    public function loadContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId, Language::ALL);

        return new RestContentType(
            $contentType,
            $contentType->getFieldDefinitions()->toArray()
        );
    }

    /**
     * Returns a list of content types.
     *
     * @return ContentTypeList|ContentTypeInfoList
     */
    public function listContentTypes(Request $request)
    {
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.contenttypelist') {
            $return = new ContentTypeList([], $request->getPathInfo());
        } else {
            $return = new ContentTypeInfoList([], $request->getPathInfo());
        }

        if ($request->query->has('identifier')) {
            $return->contentTypes = [$this->loadContentTypeByIdentifier($request)];

            return $return;
        }

        if ($request->query->has('remoteId')) {
            $return->contentTypes = [
                $this->loadContentTypeByRemoteId($request),
            ];

            return $return;
        }

        $limit = null;
        if ($request->query->has('limit')) {
            $limit = (int)$request->query->get('limit', null);
            if ($limit <= 0) {
                throw new BadRequestException('wrong value for limit parameter');
            }
        }
        $contentTypes = $this->getContentTypeList();
        $sort = $request->query->get('sort');
        if ($request->query->has('orderby')) {
            $orderby = $request->query->get('orderby');
            $this->sortContentTypeList($contentTypes, $orderby, $sort);
        }
        $offset = $request->query->getInt('offset');
        $return->contentTypes = array_slice($contentTypes, $offset, $limit);

        return $return;
    }

    /**
     * Loads a content type by its identifier.
     *
     * @return APIContentType
     */
    public function loadContentTypeByIdentifier(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByIdentifier(
            $request->query->get('identifier'),
            Language::ALL
        );
    }

    /**
     * Loads a content type by its remote ID.
     *
     * @return APIContentType
     */
    public function loadContentTypeByRemoteId(Request $request)
    {
        return $this->contentTypeService->loadContentTypeByRemoteId(
            $request->query->get('remoteId'),
            Language::ALL
        );
    }

    /**
     * Creates a new content type draft in the given content type group.
     *
     * @param int|string $contentTypeGroupId
     *
     * @throws ForbiddenException
     * @throws BadRequestException
     *
     * @return CreatedContentType
     */
    public function createContentType(
        $contentTypeGroupId,
        Request $request
    ) {
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId);
        $publish = ($request->query->has('publish') && $request->query->get('publish') === 'true');

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $this->inputDispatcher->parse(
                    new Message(
                        [
                            'Content-Type' => $request->headers->get('Content-Type'),
                            // @todo Needs refactoring! Temporary solution so parser has access to get parameters
                            '__publish' => $publish,
                        ],
                        $request->getContent()
                    )
                ),
                [$contentTypeGroup]
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (ContentTypeValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (Exceptions\Parser $e) {
            throw new BadRequestException($e->getMessage());
        }

        if ($publish) {
            $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

            $contentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

            return new CreatedContentType(
                [
                    'contentType' => new RestContentType(
                        $contentType,
                        $contentType->getFieldDefinitions()->toArray()
                    ),
                ]
            );
        }

        return new CreatedContentType(
            [
                'contentType' => new RestContentType(
                    $contentTypeDraft,
                    $contentTypeDraft->getFieldDefinitions()->toArray()
                ),
            ]
        );
    }

    /**
     * Copies a content type. The identifier of the copy is changed to
     * copy_of_<originalBaseIdentifier>_<newTypeId> and a new remoteId is generated.
     *
     * @param int|string $contentTypeId
     *
     * @return ResourceCreated
     */
    public function copyContentType($contentTypeId)
    {
        $copiedContentType = $this->contentTypeService->copyContentType(
            $this->contentTypeService->loadContentType((int)$contentTypeId)
        );

        return new ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content_type',
                ['contentTypeId' => $copiedContentType->id]
            )
        );
    }

    /**
     * Creates a draft and updates it with the given data.
     *
     * @param int|string $contentTypeId
     *
     * @throws ForbiddenException
     *
     * @return CreatedContentType
     */
    public function createContentTypeDraft(
        $contentTypeId,
        Request $request
    ) {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId);

        try {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft(
                $contentType
            );
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new CreatedContentType(
            [
                'contentType' => new RestContentType(
                    // Reload the content type draft to get the updated values
                    $this->contentTypeService->loadContentTypeDraft(
                        $contentTypeDraft->id
                    )
                ),
            ]
        );
    }

    /**
     * Loads a content type draft.
     *
     * @param int|string $contentTypeId
     *
     * @return RestContentType
     */
    public function loadContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);

        return new RestContentType(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()->toArray()
        );
    }

    /**
     * Updates meta data of a draft. This method does not handle field definitions.
     *
     * @param int|string $contentTypeId
     *
     * @throws ForbiddenException
     *
     * @return RestContentType
     */
    public function updateContentTypeDraft(
        $contentTypeId,
        Request $request
    ) {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);
        $contentTypeUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->updateContentTypeDraft(
                $contentTypeDraft,
                $contentTypeUpdateStruct
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new RestContentType(
            // Reload the content type draft to get the updated values
            $this->contentTypeService->loadContentTypeDraft(
                $contentTypeDraft->id
            )
        );
    }

    /**
     * Creates a new field definition for the given content type draft.
     *
     * @param int|string $contentTypeId
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     *
     * @return CreatedFieldDefinition
     */
    public function addContentTypeDraftFieldDefinition(
        $contentTypeId,
        Request $request
    ) {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int) $contentTypeId);
        $fieldDefinitionCreate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                ],
                $request->getContent()
            )
        );

        try {
            $this->contentTypeService->addFieldDefinition(
                $contentTypeDraft,
                $fieldDefinitionCreate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        } catch (ContentTypeFieldDefinitionValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->identifier == $fieldDefinitionCreate->identifier) {
                return new CreatedFieldDefinition(
                    [
                        'fieldDefinition' => new RestFieldDefinition($updatedDraft, $fieldDefinition),
                    ]
                );
            }
        }

        throw new NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Loads field definitions for a given content type.
     *
     * @param int|string $contentTypeId
     *
     * @return FieldDefinitionList
     *
     * @todo Check why this isn't in the specs
     */
    public function loadContentTypeFieldDefinitionList($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId, Language::ALL);

        return new FieldDefinitionList(
            $contentType,
            $contentType->getFieldDefinitions()->toArray()
        );
    }

    /**
     * Returns the field definition given by id.
     *
     * @param int|string $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws NotFoundException
     *
     * @return RestFieldDefinition
     */
    public function loadContentTypeFieldDefinition(
        $contentTypeId,
        $fieldDefinitionId,
        Request $request
    ) {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId, Language::ALL);

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new RestFieldDefinition(
                    $contentType,
                    $fieldDefinition
                );
            }
        }

        throw new NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * @throws NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function loadContentTypeFieldDefinitionByIdentifier(
        int $contentTypeId,
        string $fieldDefinitionIdentifier,
        Request $request
    ): RestFieldDefinition {
        $contentType = $this->contentTypeService->loadContentType($contentTypeId);
        $fieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);
        $path = $this->router->generate(
            'ibexa.rest.load_content_type_field_definition_by_identifier',
            [
                'contentTypeId' => $contentType->id,
                'fieldDefinitionIdentifier' => $fieldDefinitionIdentifier,
            ]
        );

        if ($fieldDefinition === null) {
            throw new NotFoundException(
                sprintf("Field definition not found: '%s'.", $request->getPathInfo())
            );
        }

        return new RestFieldDefinition(
            $contentType,
            $fieldDefinition,
            $path
        );
    }

    /**
     * Loads field definitions for a given content type draft.
     *
     * @param $contentTypeId
     *
     * @return FieldDefinitionList
     */
    public function loadContentTypeDraftFieldDefinitionList($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentTypeId);

        return new FieldDefinitionList(
            $contentTypeDraft,
            $contentTypeDraft->getFieldDefinitions()->toArray(),
        );
    }

    /**
     * Returns the draft field definition given by id.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws NotFoundException
     *
     * @return RestFieldDefinition
     */
    public function loadContentTypeDraftFieldDefinition(
        $contentTypeId,
        $fieldDefinitionId,
        Request $request
    ) {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);

        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id == $fieldDefinitionId) {
                return new RestFieldDefinition(
                    $contentTypeDraft,
                    $fieldDefinition
                );
            }
        }

        throw new NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Updates the attributes of a field definition.
     *
     * @param $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     *
     * @return FieldDefinitionList
     */
    public function updateContentTypeDraftFieldDefinition(
        $contentTypeId,
        $fieldDefinitionId,
        Request $request
    ) {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);
        $fieldDefinitionUpdate = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    // @todo Needs refactoring! Temporary solution so parser has access to URL
                    'Url' => $request->getPathInfo(),
                ],
                $request->getContent()
            )
        );

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        try {
            $this->contentTypeService->updateFieldDefinition(
                $contentTypeDraft,
                $fieldDefinition,
                $fieldDefinitionUpdate
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $updatedDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);
        foreach ($updatedDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                return new RestFieldDefinition($updatedDraft, $fieldDef);
            }
        }

        throw new NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Deletes a field definition from a content type draft.
     *
     * @param int|string $contentTypeId
     * @param $fieldDefinitionId
     *
     * @throws NotFoundException
     *
     * @return NoContent
     */
    public function removeContentTypeDraftFieldDefinition(
        $contentTypeId,
        $fieldDefinitionId,
        Request $request
    ) {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);

        $fieldDefinition = null;
        foreach ($contentTypeDraft->getFieldDefinitions() as $fieldDef) {
            if ($fieldDef->id == $fieldDefinitionId) {
                $fieldDefinition = $fieldDef;
            }
        }

        if ($fieldDefinition === null) {
            throw new NotFoundException("Field definition not found: '{$request->getPathInfo()}'.");
        }

        $this->contentTypeService->removeFieldDefinition(
            $contentTypeDraft,
            $fieldDefinition
        );

        return new NoContent();
    }

    /**
     * Publishes a content type draft.
     *
     * @param int|string $contentTypeId
     *
     * @throws ForbiddenException
     *
     * @return RestContentType
     */
    public function publishContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);

        $fieldDefinitions = $contentTypeDraft->getFieldDefinitions();
        if (empty($fieldDefinitions)) {
            throw new ForbiddenException('Cannot publish an empty content type draft');
        }

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $publishedContentType = $this->contentTypeService->loadContentType($contentTypeDraft->id, Language::ALL);

        return new RestContentType(
            $publishedContentType,
            $publishedContentType->getFieldDefinitions()->toArray()
        );
    }

    /**
     * The given content type is deleted.
     *
     * @param int|string $contentTypeId
     *
     * @throws ForbiddenException
     *
     * @return NoContent
     */
    public function deleteContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId);

        try {
            $this->contentTypeService->deleteContentType($contentType);
        } catch (BadStateException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        return new NoContent();
    }

    /**
     * The given content type draft is deleted.
     *
     * @param int|string $contentTypeId
     *
     * @return NoContent
     */
    public function deleteContentTypeDraft($contentTypeId)
    {
        $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft((int)$contentTypeId);
        $this->contentTypeService->deleteContentType($contentTypeDraft);

        return new NoContent();
    }

    /**
     * Returns the content type groups the content type belongs to.
     *
     * @param int|string $contentTypeId
     *
     * @return ContentTypeGroupRefList
     */
    public function loadGroupsOfContentType($contentTypeId)
    {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId, Language::ALL);

        return new ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    /**
     * Links a content type group to the content type and returns the updated group list.
     *
     * @param mixed $contentTypeId
     *
     * @throws ForbiddenException
     * @throws BadRequestException
     *
     * @return ContentTypeGroupRefList
     */
    public function linkContentTypeToGroup(
        $contentTypeId,
        Request $request
    ) {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId);

        try {
            $contentTypeGroupId = $this->requestParser->parseHref(
                $request->query->get('group'),
                'contentTypeGroupId'
            );
        } catch (Exceptions\InvalidArgumentException $e) {
            // Group URI does not match the required value
            throw new BadRequestException($e->getMessage());
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if ($contentTypeInGroup) {
            throw new ForbiddenException('The content type is already linked to the provided group');
        }

        $this->contentTypeService->assignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $existingContentTypeGroups[] = $contentTypeGroup;

        return new ContentTypeGroupRefList(
            $contentType,
            $existingContentTypeGroups
        );
    }

    /**
     * Removes the given group from the content type and returns the updated group list.
     *
     * @param int|string $contentTypeId
     * @param int|string $contentTypeGroupId
     *
     * @throws ForbiddenException
     * @throws NotFoundException
     *
     * @return ContentTypeGroupRefList
     */
    public function unlinkContentTypeFromGroup(
        $contentTypeId,
        $contentTypeGroupId
    ) {
        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId);
        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroup((int)$contentTypeGroupId);

        $existingContentTypeGroups = $contentType->getContentTypeGroups();
        $contentTypeInGroup = false;
        foreach ($existingContentTypeGroups as $existingGroup) {
            if ($existingGroup->id == $contentTypeGroup->id) {
                $contentTypeInGroup = true;
                break;
            }
        }

        if (!$contentTypeInGroup) {
            throw new NotFoundException('The content type is not in the provided group');
        }

        if (count($existingContentTypeGroups) == 1) {
            throw new ForbiddenException('Cannot unlink the content type from its only remaining group');
        }

        $this->contentTypeService->unassignContentTypeGroup(
            $contentType,
            $contentTypeGroup
        );

        $contentType = $this->contentTypeService->loadContentType((int)$contentTypeId);

        return new ContentTypeGroupRefList(
            $contentType,
            $contentType->getContentTypeGroups()
        );
    }

    public function createView(Request $request): ContentTypeList
    {
        /** @var ContentTypeRestViewInput $viewInput */
        $viewInput = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentTypes = $this->contentTypeService->findContentTypes($viewInput->query);

        return new ContentTypeList(
            $contentTypes->getContentTypes(),
            '',
        );
    }

    /**
     * Converts the provided ContentTypeGroupCreateStruct to ContentTypeGroupUpdateStruct.
     *
     * @param ContentTypeGroupCreateStruct $createStruct
     *
     * @return ContentTypeGroupUpdateStruct
     */
    private function mapToGroupUpdateStruct(ContentTypeGroupCreateStruct $createStruct)
    {
        return new ContentTypeGroupUpdateStruct(
            [
                'identifier' => $createStruct->identifier,
                'modifierId' => $createStruct->creatorId,
                'modificationDate' => $createStruct->creationDate,
            ]
        );
    }

    /**
     * @param array &$contentTypes
     * @param string $orderby
     *
     * @return mixed
     *
     * @throws BadRequestException
     */
    protected function sortContentTypeList(
        array &$contentTypes,
        $orderby,
        $sort = 'asc'
    ) {
        switch ($orderby) {
            case 'name':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        static function (
                            APIContentType $contentType1,
                            APIContentType $contentType2
                        ) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier);
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function (
                            APIContentType $contentType1,
                            APIContentType $contentType2
                        ) {
                            return strcasecmp($contentType1->identifier, $contentType2->identifier) * -1;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            case 'lastmodified':
                if ($sort === 'asc' || $sort === null) {
                    usort(
                        $contentTypes,
                        static function (
                            $timeObj3,
                            $timeObj4
                        ) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 > $timeObj4;
                        }
                    );
                } elseif ($sort === 'desc') {
                    usort(
                        $contentTypes,
                        static function (
                            $timeObj3,
                            $timeObj4
                        ) {
                            $timeObj3 = strtotime($timeObj3->modificationDate->format('Y-m-d H:i:s'));
                            $timeObj4 = strtotime($timeObj4->modificationDate->format('Y-m-d H:i:s'));

                            return $timeObj3 < $timeObj4;
                        }
                    );
                } else {
                    throw new BadRequestException('wrong value for sort parameter');
                }
                break;
            default:
                throw new BadRequestException('wrong value for orderby parameter');
                break;
        }
    }

    /**
     * @return ContentType[]
     */
    protected function getContentTypeList()
    {
        $contentTypes = [];
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            $contentTypes = array_merge(
                $contentTypes,
                $this->contentTypeService->loadContentTypes($contentTypeGroup, Language::ALL)
            );
        }

        return $contentTypes;
    }
}

class_alias(ContentType::class, 'EzSystems\EzPlatformRest\Server\Controller\ContentType');
