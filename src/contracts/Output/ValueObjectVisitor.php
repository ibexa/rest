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

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $templateRouter;

    /**
     * Visit struct returned by controllers.
     *
     * @param \Ibexa\Contracts\Rest\Output\Visitor $visitor
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param mixed $data
     */
    abstract public function visit(Visitor $visitor, Generator $generator, $data);

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
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
     *
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param bool $boolValue
     *
     * @return mixed
     */
    protected function serializeBool(Generator $generator, $boolValue)
    {
        return $generator->serializeBool($boolValue);
    }

    /**
     * Visits the given list of $names.
     *
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param array $names
     */
    protected function visitNamesList(Generator $generator, array $names)
    {
        $this->visitTranslatedList($generator, $names, 'names');
    }

    /**
     * Visits the given list of $descriptions.
     *
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param array $descriptions
     */
    protected function visitDescriptionsList(Generator $generator, array $descriptions)
    {
        $this->visitTranslatedList($generator, $descriptions, 'descriptions');
    }

    /**
     * Visits a list of translated elements.
     *
     * @param array $translatedElements
     */
    protected function visitTranslatedList(Generator $generator, array $translatedElements, string $listName)
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
     *
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Contracts\Core\Repository\Values\User\Limitation $limitation
     */
    protected function visitLimitation(Generator $generator, Limitation $limitation)
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
     *
     * @param int $sortField
     *
     * @return string
     */
    protected function serializeSortField($sortField)
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
     *
     * @param int $sortOrder
     *
     * @return string
     */
    protected function serializeSortOrder($sortOrder)
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
