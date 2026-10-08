<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct;
use Ibexa\Contracts\Core\Repository\Values\Content\SectionUpdateStruct;
use Ibexa\Rest\Message;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Exceptions\ForbiddenException;
use Ibexa\Rest\Server\Values\CreatedSection;
use Ibexa\Rest\Server\Values\NoContent;
use Ibexa\Rest\Server\Values\SectionList;
use Symfony\Component\HttpFoundation\Request;

/**
 * Section controller.
 */
class Section extends RestController
{
    /**
     * Section service.
     *
     * @var SectionService
     */
    protected $sectionService;

    /**
     * Construct controller.
     *
     * @param SectionService $sectionService
     */
    public function __construct(SectionService $sectionService)
    {
        $this->sectionService = $sectionService;
    }

    /**
     * List sections.
     *
     * @return SectionList
     */
    public function listSections(Request $request)
    {
        if ($request->query->has('identifier')) {
            $sections = [
                $this->loadSectionByIdentifier($request),
            ];
        } else {
            $sections = $this->sectionService->loadSections();
        }

        return new SectionList($sections, $request->getPathInfo());
    }

    /**
     * Loads section by identifier.
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section
     */
    public function loadSectionByIdentifier(Request $request)
    {
        return $this->sectionService->loadSectionByIdentifier(
            // GET variable
            $request->query->get('identifier')
        );
    }

    /**
     * Create new section.
     *
     * @throws ForbiddenException
     *
     * @return CreatedSection
     */
    public function createSection(Request $request)
    {
        try {
            $createdSection = $this->sectionService->createSection(
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

        return new CreatedSection(
            [
                'section' => $createdSection,
            ]
        );
    }

    /**
     * Loads a section.
     *
     * @param $sectionId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section
     */
    public function loadSection($sectionId)
    {
        return $this->sectionService->loadSection($sectionId);
    }

    /**
     * Updates a section.
     *
     * @param $sectionId
     *
     * @throws ForbiddenException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section
     */
    public function updateSection(
        $sectionId,
        Request $request
    ) {
        $createStruct = $this->inputDispatcher->parse(
            new Message(
                ['Content-Type' => $request->headers->get('Content-Type')],
                $request->getContent()
            )
        );

        try {
            return $this->sectionService->updateSection(
                $this->sectionService->loadSection($sectionId),
                $this->mapToUpdateStruct($createStruct)
            );
        } catch (InvalidArgumentException $e) {
            throw new ForbiddenException(/** @Ignore */ $e->getMessage());
        }
    }

    /**
     * Delete a section by ID.
     *
     * @param $sectionId
     *
     * @return NoContent
     */
    public function deleteSection($sectionId)
    {
        $this->sectionService->deleteSection(
            $this->sectionService->loadSection($sectionId)
        );

        return new NoContent();
    }

    /**
     * Maps a SectionCreateStruct to a SectionUpdateStruct.
     *
     * Needed since both structs are encoded into the same media type on input.
     *
     * @param SectionCreateStruct $createStruct
     *
     * @return SectionUpdateStruct
     */
    protected function mapToUpdateStruct(SectionCreateStruct $createStruct)
    {
        return new SectionUpdateStruct(
            [
                'name' => $createStruct->name,
                'identifier' => $createStruct->identifier,
            ]
        );
    }
}

class_alias(Section::class, 'EzSystems\EzPlatformRest\Server\Controller\Section');
