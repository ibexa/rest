<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Output;

use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Rest\UriParser\UriParserInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Basic ValueObjectVisitor.
 */
abstract class ValueObjectVisitor
{
    protected UriParserInterface $uriParser;

    protected RouterInterface $router;

    protected RouterInterface $templateRouter;

    /**
     * Visit struct returned by controllers.
     */
    abstract public function visit(Visitor $visitor, Generator $generator, mixed $data);

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setTemplateRouter(RouterInterface $templateRouter): void
    {
        $this->templateRouter = $templateRouter;
    }

    public function setUriParser(UriParserInterface $uriParser): void
    {
        $this->uriParser = $uriParser;
    }

    /**
     * Returns a string representation for the given $boolValue.
     */
    protected function serializeBool(Generator $generator, ?bool $boolValue): string|bool
    {
        return $generator->serializeBool($boolValue);
    }

    /**
     * Visits the given list of $names.
     */
    protected function visitNamesList(Generator $generator, array $names): void
    {
        $this->visitTranslatedList($generator, $names, 'names');
    }

    /**
     * Visits the given list of $descriptions.
     */
    protected function visitDescriptionsList(Generator $generator, array $descriptions): void
    {
        $this->visitTranslatedList($generator, $descriptions, 'descriptions');
    }

    /**
     * Visits a list of translated elements.
     */
    protected function visitTranslatedList(Generator $generator, array $translatedElements, string $listName): void
    {
        $generator->startHashElement($listName);
        $generator->startList('value');
        foreach ($translatedElements as $languageCode => $element) {
            $generator->startValueElement('value', $element, ['languageCode' => $languageCode]);
            $generator->endValueElement('value');
        }
        $generator->endList('value');
        $generator->endHashElement($listName);
    }

    /**
     * Visits a limitation.
     */
    protected function visitLimitation(Generator $generator, Limitation $limitation): void
    {
        $generator->startHashElement('limitation');

        $generator->startAttribute('identifier', $limitation->getIdentifier());
        $generator->endAttribute('identifier');

        $generator->startHashElement('values');
        $generator->startList('ref');

        foreach ($limitation->limitationValues as $limitationValue) {
            $generator->startObjectElement('ref');
            $generator->startAttribute('href', $limitationValue);
            $generator->endAttribute('href');
            $generator->endObjectElement('ref');
        }

        $generator->endList('ref');
        $generator->endHashElement('values');

        $generator->endHashElement('limitation');
    }

    /**
     * Serializes the given $sortField to a string representation.
     */
    protected function serializeSortField(int $sortField): string
    {
        switch ($sortField) {
            case Location::SORT_FIELD_PATH:
                return 'PATH';
            case Location::SORT_FIELD_PUBLISHED:
                return 'PUBLISHED';
            case Location::SORT_FIELD_MODIFIED:
                return 'MODIFIED';
            case Location::SORT_FIELD_SECTION:
                return 'SECTION';
            case Location::SORT_FIELD_DEPTH:
                return 'DEPTH';
            case Location::SORT_FIELD_CLASS_IDENTIFIER:
                return 'CLASS_IDENTIFIER';
            case Location::SORT_FIELD_CLASS_NAME:
                return 'CLASS_NAME';
            case Location::SORT_FIELD_PRIORITY:
                return 'PRIORITY';
            case Location::SORT_FIELD_NAME:
                return 'NAME';
            case Location::SORT_FIELD_NODE_ID:
                return 'NODE_ID';
            case Location::SORT_FIELD_CONTENTOBJECT_ID:
                return 'CONTENTOBJECT_ID';
        }

        throw new \RuntimeException("Unknown default sort Field: '{$sortField}'.");
    }

    /**
     * Serializes the given $sortOrder to a string representation.
     */
    protected function serializeSortOrder(int $sortOrder): string
    {
        switch ($sortOrder) {
            case Location::SORT_ORDER_ASC:
                return 'ASC';
            case Location::SORT_ORDER_DESC:
                return 'DESC';
        }

        throw new \RuntimeException("Unknown default sort order: '{$sortOrder}'.");
    }
}
