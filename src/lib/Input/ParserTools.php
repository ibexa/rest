<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Input;

use Ibexa\Contracts\Core\Repository\Values;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LanguageLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\LocationLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ObjectStateLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\OwnerLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentContentTypeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentDepthLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentOwnerLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\ParentUserGroupLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SectionLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SiteAccessLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\SubtreeLimitation;
use Ibexa\Contracts\Core\Repository\Values\User\Limitation\UserGroupLimitation;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use RuntimeException;

/**
 * Tools object to be used in Input Parsers.
 */
class ParserTools
{
    /**
     * Parses the given $objectElement, if it contains embedded data.
     */
    public function parseObjectElement(array $objectElement, ParsingDispatcher $parsingDispatcher): string
    {
        if ($this->isEmbeddedObject($objectElement)) {
            $parsingDispatcher->parse(
                $objectElement,
                $objectElement['_media-type']
            );
        }

        return $objectElement['_href'];
    }

    /**
     * Returns if the given $objectElement has embedded object data or is only
     * a reference.
     */
    public function isEmbeddedObject(array $objectElement): bool
    {
        foreach ($objectElement as $childKey => $childValue) {
            $childKeyIndicator = substr($childKey, 0, 1);
            if ($childKeyIndicator !== '#' && $childKeyIndicator !== '_') {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses a translatable list, like names or descriptions.
     */
    public function parseTranslatableList(array $listElement): array
    {
        $listItems = [];
        foreach ($listElement['value'] as $valueRow) {
            $listItems[$valueRow['_languageCode']] = isset($valueRow['#text']) ?
                $valueRow['#text'] :
                '';
        }

        return $listItems;
    }

    /***
     * @throws \RuntimeException if the value can not be transformed to a boolean
     */
    public function parseBooleanValue(string|bool $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
        }

        throw new RuntimeException("Unknown boolean value '{$value}'.");
    }

    /**
     * Parses the content types status from $contentTypeStatus.
     */
    public function parseStatus(string $contentTypeStatus): int
    {
        switch (strtoupper($contentTypeStatus)) {
            case 'DEFINED':
                return Values\ContentType\ContentType::STATUS_DEFINED;
            case 'DRAFT':
                return Values\ContentType\ContentType::STATUS_DRAFT;
            case 'MODIFIED':
                return Values\ContentType\ContentType::STATUS_MODIFIED;
        }

        throw new RuntimeException("Unknown content type status '{$contentTypeStatus}.'");
    }

    /**
     * Parses the default sort field from the given $defaultSortFieldString.
     */
    public function parseDefaultSortField(string $defaultSortFieldString): int
    {
        switch ($defaultSortFieldString) {
            case 'PATH':
                return Values\Content\Location::SORT_FIELD_PATH;
            case 'PUBLISHED':
                return Values\Content\Location::SORT_FIELD_PUBLISHED;
            case 'MODIFIED':
                return Values\Content\Location::SORT_FIELD_MODIFIED;
            case 'SECTION':
                return Values\Content\Location::SORT_FIELD_SECTION;
            case 'DEPTH':
                return Values\Content\Location::SORT_FIELD_DEPTH;
            case 'CLASS_IDENTIFIER':
                return Values\Content\Location::SORT_FIELD_CLASS_IDENTIFIER;
            case 'CLASS_NAME':
                return Values\Content\Location::SORT_FIELD_CLASS_NAME;
            case 'PRIORITY':
                return Values\Content\Location::SORT_FIELD_PRIORITY;
            case 'NAME':
                return Values\Content\Location::SORT_FIELD_NAME;
            case 'NODE_ID':
                return Values\Content\Location::SORT_FIELD_NODE_ID;
            case 'CONTENTOBJECT_ID':
                return Values\Content\Location::SORT_FIELD_CONTENTOBJECT_ID;
        }

        throw new RuntimeException("Unknown default sort Field: '{$defaultSortFieldString}'.");
    }

    /**
     * Parses the default sort order from the given $defaultSortOrderString.
     */
    public function parseDefaultSortOrder(string $defaultSortOrderString): int
    {
        switch (strtoupper($defaultSortOrderString)) {
            case 'ASC':
                return Values\Content\Location::SORT_ORDER_ASC;
            case 'DESC':
                return Values\Content\Location::SORT_ORDER_DESC;
        }

        throw new RuntimeException("Unknown default sort order: '{$defaultSortOrderString}'.");
    }

    /**
     * Parses the input structure to Limitation object.
     */
    public function parseLimitation(array $limitation): Limitation
    {
        if (!array_key_exists('_identifier', $limitation)) {
            throw new Exceptions\Parser("Missing '_identifier' attribute for Limitation.");
        }

        $limitationObject = $this->getLimitationByIdentifier($limitation['_identifier']);

        if (!isset($limitation['values']['ref']) || !is_array($limitation['values']['ref'])) {
            throw new Exceptions\Parser('Invalid format for Limitation value in Limitation.');
        }

        $limitationValues = [];
        foreach ($limitation['values']['ref'] as $limitationValue) {
            if (!array_key_exists('_href', $limitationValue)) {
                throw new Exceptions\Parser('Invalid format for Limitation value in Limitation.');
            }

            $limitationValues[] = $limitationValue['_href'];
        }

        $limitationObject->limitationValues = $limitationValues;

        return $limitationObject;
    }

    /**
     * Instantiates Limitation object based on identifier.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     *
     * @todo Use dependency injection system
     */
    protected function getLimitationByIdentifier(string $identifier): Limitation
    {
        switch ($identifier) {
            case Limitation::CONTENTTYPE:
                return new ContentTypeLimitation();

            case Limitation::LANGUAGE:
                return new LanguageLimitation();

            case Limitation::LOCATION:
                return new LocationLimitation();

            case Limitation::OWNER:
                return new OwnerLimitation();

            case Limitation::PARENTOWNER:
                return new ParentOwnerLimitation();

            case Limitation::PARENTCONTENTTYPE:
                return new ParentContentTypeLimitation();

            case Limitation::PARENTDEPTH:
                return new ParentDepthLimitation();

            case Limitation::SECTION:
                return new SectionLimitation();

            case Limitation::SITEACCESS:
                return new SiteAccessLimitation();

            case Limitation::STATE:
                return new ObjectStateLimitation();

            case Limitation::SUBTREE:
                return new SubtreeLimitation();

            case Limitation::USERGROUP:
                return new UserGroupLimitation();

            case Limitation::PARENTUSERGROUP:
                return new ParentUserGroupLimitation();

            default:
                throw new NotFoundException('Limitation', $identifier);
        }
    }
}
