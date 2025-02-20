<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use Exception;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Rest\Exceptions;
use function Ibexa\PolyfillPhp82\iterator_to_array;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use Ibexa\Rest\Server\Exceptions\ContentFieldValidationException as RESTContentFieldValidationException;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values;
use Ibexa\Rest\Server\Values\RestContentCreateStruct;
use JMS\TranslationBundle\Annotation\Ignore;
use LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Content extends RestController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly HttpKernelInterface $kernel,
        private readonly ContentService $contentService,
        private readonly ContentService\RelationListFacadeInterface $relationListFacade
    ) {
    }

    /**
     * Loads a content info by remote ID.
     *
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectContent(Request $request)
    {
        if (!$request->query->has('remoteId')) {
            throw new BadRequestException("'remoteId' parameter is required.");
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfoByRemoteId(
            (string)$request->query->get('remoteId')
        );

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content',
                [
                    'contentId' => $contentInfo->id,
                ]
            )
        );
    }

    /**
     * Loads a content info, potentially with the current version embedded.
     *
     * @param mixed $contentId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\Rest\Server\Values\RestContent
     */
    public function loadContent($contentId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $mainLocation = null;
        if (!empty($contentInfo->mainLocationId)) {
            $mainLocation = $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
        }

        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);

        $contentVersion = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.content') {
            $languages = $this->getLanguages($request);

            $contentVersion = $this->repository->getContentService()->loadContent($contentId, $languages);
            $relations = $this->relationListFacade->getRelations($contentVersion->getVersionInfo());
        }

        $restContent = new Values\RestContent(
            $contentInfo,
            $mainLocation,
            $contentVersion,
            $contentType,
            $relations !== null ? iterator_to_array($relations) : null,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $restContent;
        }

        return new Values\CachedValue(
            $restContent,
            ['locationId' => $contentInfo->mainLocationId]
        );
    }

    /**
     * Updates a content's metadata.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\RestContent
     */
    public function updateContentMetadata($contentId, Request $request)
    {
        $updateStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        // update section
        if ($updateStruct->sectionId !== null) {
            $section = $this->repository->getSectionService()->loadSection($updateStruct->sectionId);
            $this->repository->getSectionService()->assignSection($contentInfo, $section);
            $updateStruct->sectionId = null;
        }

        // @todo Consider refactoring! ContentService::updateContentMetadata throws the same exception
        // in case the updateStruct is empty and if remoteId already exists. Since REST version of update struct
        // includes section ID in addition to other fields, we cannot throw exception if only sectionId property
        // is set, so we must skip updating content in that case instead of allowing propagation of the exception.
        foreach ($updateStruct as $propertyName => $propertyValue) {
            if ($propertyName !== 'sectionId' && $propertyValue !== null) {
                // update content
                $this->repository->getContentService()->updateContentMetadata($contentInfo, $updateStruct);
                $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
                break;
            }
        }

        try {
            $locationInfo = null !== $contentInfo->mainLocationId
                ? $this->repository->getLocationService()->loadLocation($contentInfo->mainLocationId)
                : null;
        } catch (NotFoundException $e) {
            $locationInfo = null;
        }

        return new Values\RestContent(
            $contentInfo,
            $locationInfo
        );
    }

    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.load_content_in_version',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                ]
            )
        );
    }

    /**
     * Loads a specific version of a given content object.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\Version
     */
    public function loadContentInVersion($contentId, $versionNumber, Request $request)
    {
        $languages = $this->getLanguages($request);

        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionNumber
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        $versionValue = new Values\Version(
            $content,
            $contentType,
            iterator_to_array($this->relationListFacade->getRelations($content->getVersionInfo())),
            $request->getPathInfo()
        );

        if ($content->contentInfo->mainLocationId === null || $content->versionInfo->status === VersionInfo::STATUS_DRAFT) {
            return $versionValue;
        }

        return new Values\CachedValue(
            $versionValue,
            ['locationId' => $content->contentInfo->mainLocationId]
        );
    }

    /**
     * Creates a new content draft assigned to the authenticated user.
     * If a different userId is given in the input it is assigned to the
     * given user but this required special rights for the authenticated
     * user (this is useful for content staging where the transfer process
     * does not have to authenticate with the user which created the content
     * object in the source server). The user has to publish the content if
     * it should be visible.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContent
     */
    public function createContent(Request $request)
    {
        $contentCreate = $this->parseContentRequest($request);

        return $this->doCreateContent($request, $contentCreate);
    }

    /**
     * The content is deleted. If the content has locations (which is required in 4.x)
     * on delete all locations assigned the content object are deleted via delete subtree.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContent($contentId)
    {
        $this->repository->getContentService()->deleteContent(
            $this->repository->getContentService()->loadContentInfo($contentId)
        );

        return new Values\NoContent();
    }

    /**
     * Creates a new content object as copy under the given parent location given in the destination header.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\ResourceCreated
     */
    public function copyContent($contentId, Request $request)
    {
        /** @var string $destination */
        $destination = $request->headers->get('Destination');

        $parentLocationParts = explode('/', $destination);
        $copiedContent = $this->repository->getContentService()->copyContent(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $this->repository->getLocationService()->newLocationCreateStruct(array_pop($parentLocationParts))
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $copiedContent->id]
            )
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function copy(int $contentId, Request $request): Values\ResourceCreated
    {
        $contentService = $this->repository->getContentService();
        $locationService = $this->repository->getLocationService();

        $contentInfo = $contentService->loadContentInfo($contentId);

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Location $destinationLocation */
        $destinationLocation = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent(),
            ),
        );

        $copiedContent = $contentService->copyContent(
            $contentInfo,
            $locationService->newLocationCreateStruct($destinationLocation->getId()),
        );

        return new Values\ResourceCreated(
            $this->router->generate(
                'ibexa.rest.load_content',
                ['contentId' => $copiedContent->id],
            )
        );
    }

    /**
     * Deletes a translation from all the Versions of the given Content Object.
     *
     * If any non-published Version contains only the Translation to be deleted, that entire Version will be deleted
     *
     * @param int $contentId
     * @param string $languageCode
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     *
     * @throws \Exception
     */
    public function deleteContentTranslation($contentId, $languageCode)
    {
        $contentService = $this->repository->getContentService();

        $this->repository->beginTransaction();
        try {
            $contentInfo = $contentService->loadContentInfo($contentId);
            $contentService->deleteTranslation(
                $contentInfo,
                $languageCode
            );

            $this->repository->commit();

            return new Values\NoContent();
        } catch (Exception $e) {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Returns a list of all versions of the content. This method does not
     * include fields and relations in the Version elements of the response.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function loadContentVersions(int $contentId, Request $request): Values\VersionList
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\VersionList(
            iterator_to_array($this->repository->getContentService()->loadVersions($contentInfo)),
            $request->getPathInfo()
        );
    }

    /**
     * The version is deleted.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function deleteContentVersion($contentId, $versionNumber)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if ($versionInfo->isPublished()) {
            throw new ForbiddenException('Versions with PUBLISHED status cannot be deleted');
        }

        $this->repository->getContentService()->deleteVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * Remove the given Translation from the given Version Draft.
     *
     * @param int $contentId
     * @param int $versionNumber
     * @param string $languageCode
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     */
    public function deleteTranslationFromDraft($contentId, $versionNumber, $languageCode)
    {
        $contentService = $this->repository->getContentService();
        $versionInfo = $contentService->loadVersionInfoById($contentId, $versionNumber);

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Translation can be deleted from a DRAFT version only');
        }

        $contentService->deleteTranslationFromDraft($versionInfo, $languageCode);

        return new Values\NoContent();
    }

    /**
     * The system creates a new draft version as a copy from the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\CreatedVersion
     */
    public function createDraftFromVersion($contentId, $versionNumber)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $contentDraft = $this->repository->getContentService()->createContentDraft(
            $contentInfo,
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        return new Values\CreatedVersion(
            [
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    iterator_to_array($this->relationListFacade->getRelations($contentDraft->getVersionInfo()))
                ),
            ]
        );
    }

    /**
     * The system creates a new draft version as a copy from the current version.
     *
     * @param mixed $contentId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if the current version is already a draft
     *
     * @return \Ibexa\Rest\Server\Values\CreatedVersion
     */
    public function createDraftFromCurrentVersion($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $contentType = $this->repository->getContentTypeService()->loadContentType($contentInfo->contentTypeId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $contentInfo
        );

        if ($versionInfo->isDraft()) {
            throw new ForbiddenException('Current version already has DRAFT status');
        }

        $contentDraft = $this->repository->getContentService()->createContentDraft($contentInfo);

        return new Values\CreatedVersion(
            [
                'version' => new Values\Version(
                    $contentDraft,
                    $contentType,
                    iterator_to_array($this->relationListFacade->getRelations($contentDraft->getVersionInfo()))
                ),
            ]
        );
    }

    /**
     * A specific draft is updated.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Rest\Server\Exceptions\BadRequestException
     *
     * @return \Ibexa\Rest\Server\Values\Version
     */
    public function updateVersion($contentId, $versionNumber, Request $request)
    {
        $contentUpdateStruct = $this->inputDispatcher->parse(
            new Message(
                [
                    'Content-Type' => $request->headers->get('Content-Type'),
                    'Url' => $this->router->generate(
                        'ibexa.rest.update_version',
                        [
                            'contentId' => $contentId,
                            'versionNumber' => $versionNumber,
                        ]
                    ),
                ],
                $request->getContent()
            )
        );

        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Only versions with DRAFT status can be updated');
        }

        try {
            $this->repository->getContentService()->updateContent($versionInfo, $contentUpdateStruct);
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new RESTContentFieldValidationException($e);
        }

        $languages = $this->getLanguages($request);

        // Reload the content to handle languages GET parameter
        $content = $this->repository->getContentService()->loadContent(
            $contentId,
            $languages,
            $versionInfo->versionNo
        );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->getVersionInfo()->getContentInfo()->contentTypeId
        );

        return new Values\Version(
            $content,
            $contentType,
            iterator_to_array($this->relationListFacade->getRelations($content->getVersionInfo())),
            $request->getPathInfo()
        );
    }

    /**
     * The content version is published.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if version $versionNumber isn't a draft
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function publishVersion($contentId, $versionNumber)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Only versions with DRAFT status can be published');
        }

        $this->repository->getContentService()->publishVersion(
            $versionInfo
        );

        return new Values\NoContent();
    }

    /**
     * Redirects to the relations of the current version.
     *
     * @param mixed $contentId
     *
     * @return \Ibexa\Rest\Server\Values\TemporaryRedirect
     */
    public function redirectCurrentVersionRelations($contentId)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        return new Values\TemporaryRedirect(
            $this->router->generate(
                'ibexa.rest.redirect_current_version_relations',
                [
                    'contentId' => $contentId,
                    'versionNumber' => $contentInfo->currentVersionNo,
                ]
            )
        );
    }

    /**
     * Loads the relations of the given version.
     *
     * @param mixed $contentId
     * @param mixed $versionNumber
     *
     * @return \Ibexa\Rest\Server\Values\RelationList
     */
    public function loadVersionRelations($contentId, $versionNumber, Request $request)
    {
        $offset = $request->query->has('offset') ? (int)$request->query->get('offset') : 0;
        $limit = $request->query->has('limit') ? (int)$request->query->get('limit') : -1;

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->contentService->loadRelationList(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber),
            $offset,
            $limit,
        );

        $relations = [];
        foreach ($relationList as $relationListItem) {
            if ($relationListItem->hasRelation()) {
                /** @var \Ibexa\Core\Repository\Values\Content\Relation $relation */
                $relation = $relationListItem->getRelation();
                $relations[] = $relation;
            }
        }

        $relationListValue = new Values\RelationList(
            $relations,
            $contentId,
            $versionNumber,
            $request->getPathInfo()
        );

        if ($contentInfo->mainLocationId === null) {
            return $relationListValue;
        }

        return new Values\CachedValue(
            $relationListValue,
            ['locationId' => $contentInfo->mainLocationId]
        );
    }

    /**
     * Loads a relation for the given content object and version.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     * @param mixed $relationId
     *
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\RestRelation
     */
    public function loadVersionRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $relationList = $this->relationListFacade->getRelations(
            $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber)
        );

        foreach ($relationList as $relation) {
            if ($relation->id == $relationId) {
                $relation = new Values\RestRelation($relation, $contentId, $versionNumber);

                if ($contentInfo->mainLocationId === null) {
                    return $relation;
                }

                return new Values\CachedValue(
                    $relation,
                    ['locationId' => $contentInfo->mainLocationId]
                );
            }
        }

        throw new Exceptions\NotFoundException("Relation not found: '{$request->getPathInfo()}'.");
    }

    /**
     * Deletes a relation of the given draft.
     *
     * @param mixed $contentId
     * @param int   $versionNumber
     * @param mixed $relationId
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException
     * @throws \Ibexa\Contracts\Rest\Exceptions\NotFoundException
     *
     * @return \Ibexa\Rest\Server\Values\NoContent
     */
    public function removeRelation($contentId, $versionNumber, $relationId, Request $request)
    {
        $versionInfo = $this->repository->getContentService()->loadVersionInfo(
            $this->repository->getContentService()->loadContentInfo($contentId),
            $versionNumber
        );

        $versionRelations = $this->relationListFacade->getRelations($versionInfo);
        foreach ($versionRelations as $relation) {
            if ($relation->id == $relationId) {
                if ($relation->type !== Relation::COMMON) {
                    throw new ForbiddenException('Relation is not of type COMMON');
                }

                if (!$versionInfo->isDraft()) {
                    throw new ForbiddenException('Relation of type COMMON can only be removed from drafts');
                }

                $this->repository->getContentService()->deleteRelation($versionInfo, $relation->getDestinationContentInfo());

                return new Values\NoContent();
            }
        }

        throw new Exceptions\NotFoundException("Could not find Relation '{$request->getPathInfo()}'.");
    }

    /**
     * Creates a new relation of type COMMON for the given draft.
     *
     * @param mixed $contentId
     * @param int $versionNumber
     *
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if version $versionNumber isn't a draft
     * @throws \Ibexa\Rest\Server\Exceptions\ForbiddenException if a relation to the same content already exists
     *
     * @return \Ibexa\Rest\Server\Values\CreatedRelation
     */
    public function createRelation($contentId, $versionNumber, Request $request)
    {
        $destinationContentId = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);
        $versionInfo = $this->repository->getContentService()->loadVersionInfo($contentInfo, $versionNumber);
        if (!$versionInfo->isDraft()) {
            throw new ForbiddenException('Relation of type COMMON can only be added to drafts');
        }

        try {
            $destinationContentInfo = $this->repository->getContentService()->loadContentInfo($destinationContentId);
        } catch (NotFoundException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }

        $existingRelations = $this->relationListFacade->getRelations($versionInfo);
        foreach ($existingRelations as $existingRelation) {
            if ($existingRelation->getDestinationContentInfo()->id == $destinationContentId) {
                throw new ForbiddenException('Relation of type COMMON to the selected destination content ID already exists');
            }
        }

        $relation = $this->repository->getContentService()->addRelation($versionInfo, $destinationContentInfo);

        return new Values\CreatedRelation(
            [
                'relation' => new Values\RestRelation($relation, $contentId, $versionNumber),
            ]
        );
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function hideContent(int $contentId): Values\NoContent
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->repository->getContentService()->hideContent($contentInfo);

        return new Values\NoContent();
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function revealContent(int $contentId): Values\NoContent
    {
        $contentInfo = $this->repository->getContentService()->loadContentInfo($contentId);

        $this->repository->getContentService()->revealContent($contentInfo);

        return new Values\NoContent();
    }

    /**
     * @throws \Exception
     */
    protected function forward(string $controller): Response
    {
        $path['_controller'] = $controller;
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new LogicException('No requests in the stack');
        }
        $subRequest = $request->duplicate(null, null, $path);

        return $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return mixed
     */
    protected function parseContentRequest(Request $request)
    {
        return $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type'), 'Url' => $request->getPathInfo()],
                $request->getContent()
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Ibexa\Rest\Server\Values\RestContentCreateStruct $contentCreate
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     *
     * @return \Ibexa\Rest\Server\Values\CreatedContent
     */
    protected function doCreateContent(Request $request, RestContentCreateStruct $contentCreate)
    {
        try {
            $contentCreateStruct = $contentCreate->contentCreateStruct;
            $contentCreate->locationCreateStruct->sortField = $contentCreateStruct->contentType->defaultSortField;
            $contentCreate->locationCreateStruct->sortOrder = $contentCreateStruct->contentType->defaultSortOrder;

            $content = $this->repository->getContentService()->createContent(
                $contentCreateStruct,
                [$contentCreate->locationCreateStruct]
            );
        } catch (ContentValidationException $e) {
            throw new BadRequestException($e->getMessage());
        } catch (ContentFieldValidationException $e) {
            throw new RESTContentFieldValidationException($e);
        }

        $contentValue = null;
        $contentType = null;
        $relations = null;
        if ($this->getMediaType($request) === 'application/vnd.ibexa.api.content') {
            $contentValue = $content;
            $contentType = $this->repository->getContentTypeService()->loadContentType(
                $content->getVersionInfo()->getContentInfo()->contentTypeId
            );
            $relations = $this->relationListFacade->getRelations($contentValue->getVersionInfo());
        }

        return new Values\CreatedContent(
            [
                'content' => new Values\RestContent(
                    $content->contentInfo,
                    null,
                    $contentValue,
                    $contentType,
                    $relations !== null ? iterator_to_array($relations) : null,
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    protected function getLanguages(Request $request): array
    {
        $languages = Language::ALL;
        if ($request->query->has('languages')) {
            /** @var string $languagesString */
            $languagesString = $request->query->get('languages');
            $languages = explode(',', $languagesString);
        }

        return $languages;
    }
}
