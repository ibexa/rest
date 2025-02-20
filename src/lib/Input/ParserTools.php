<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Input;

use Ibexa\Contracts\Core\Repository\Values;
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
     *
     * @param array $objectElement
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return mixed
     */
    public function parseObjectElement(array $objectElement, ParsingDispatcher $parsingDispatcher)
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
     *
     * @param array $objectElement
     *
     * @return bool
     */
    public function isEmbeddedObject(array $objectElement)
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
     *
     * @param array $listElement
     *
     * @return array
     */
    public function parseTranslatableList(array $listElement)
    {
        $listItems = [];
        foreach ($listElement['value'] as $valueRow) {
            $listItems[$valueRow['_languageCode']] = isset($valueRow['#text']) ?
                $valueRow['#text'] :
                '';
        }

        return $listItems;
    }

    /**
     * Parses a boolean from $value.
     *
     * @param string|bool $value
     *
     * @return bool
     *
     * @throws \RuntimeException if the value can not be transformed to a boolean
     */
    public function parseBooleanValue($value)
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
     *
     * @param string $contentTypeStatus
     *
     * @return int
     */
    public function parseStatus($contentTypeStatus)
    {
        switch (strtoupper($contentTypeStatus)) {
            case 'DEFINED':
                return Values\ContentType\ContentType::STATUS_DEFINED;
            case 'DRAFT':
                return Values\ContentType\ContentType::STATUS_DRAFT;
            case 'MODIFIED':
                return Values\ContentType\ContentType::STATUS_MODIFIED;
        }

        throw new \RuntimeException("Unknown content type status '{$contentTypeStatus}.'");
    }

    /**
     * Parses the default sort field from the given $defaultSortFieldString.
     *
     * @param string $defaultSortFieldString
     *
     * @return int
     */
    public function parseDefaultSortField($defaultSortFieldString)
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

        throw new \RuntimeException("Unknown default sort Field: '{$defaultSortFieldString}'.");
    }

    /**
     * Parses the default sort order from the given $defaultSortOrderString.
     *
     * @param string $defaultSortOrderString
     *
     * @return int
     */
    public function parseDefaultSortOrder($defaultSortOrderString)
    {
        switch (strtoupper($defaultSortOrderString)) {
            case 'ASC':
                return Values\Content\Location::SORT_ORDER_ASC;
            case 'DESC':
                return Values\Content\Location::SORT_ORDER_DESC;
        }

        throw new \RuntimeException("Unknown default sort order: '{$defaultSortOrderString}'.");
    }

    /**
     * Parses the input structure to Limitation object.
     *
     * @param array $limitation
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Limitation
     */
    public function parseLimitation(array $limitation)
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
     * @param string $identifier
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\User\Limitation
     *
     * @todo Use dependency injection system
     */
    protected function getLimitationByIdentifier($identifier)
    {
        switch ($identifier) {
            case Values\User\Limitation::CONTENTTYPE:
                return new ContentTypeLimitation();

            case Values\User\Limitation::LANGUAGE:
                return new LanguageLimitation();

            case Values\User\Limitation::LOCATION:
                return new LocationLimitation();

            case Values\User\Limitation::OWNER:
                return new OwnerLimitation();

            case Values\User\Limitation::PARENTOWNER:
                return new ParentOwnerLimitation();

            case Values\User\Limitation::PARENTCONTENTTYPE:
                return new ParentContentTypeLimitation();

            case Values\User\Limitation::PARENTDEPTH:
                return new ParentDepthLimitation();

            case Values\User\Limitation::SECTION:
                return new SectionLimitation();

            case Values\User\Limitation::SITEACCESS:
                return new SiteaccessLimitation();

            case Values\User\Limitation::STATE:
                return new ObjectStateLimitation();

            case Values\User\Limitation::SUBTREE:
                return new SubtreeLimitation();

            case Values\User\Limitation::USERGROUP:
                return new UserGroupLimitation();

            case Values\User\Limitation::PARENTUSERGROUP:
                return new ParentUserGroupLimitation();

            default:
                throw new NotFoundException('Limitation', $identifier);
        }
    }
}
