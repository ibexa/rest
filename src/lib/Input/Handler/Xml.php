<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Input\Handler;

use DOMElement;
use DOMNode;
use DOMNodeList;
use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\Handler;

/**
 * Input format handler base class.
 */
class Xml extends Handler
{
    /**
     * Force list for those items.
     *
     * The key defines the item in which a list is formed. A list is then
     * formed for every value in the value array.
     */
    protected array $forceList = [
        'ContentList' => [
            'Content',
        ],
        'ContentTypeList' => [
            'ContentType',
        ],
        'ContentTypeGroupRefList' => [
            'ContentTypeGroupRef',
        ],
        'SectionList' => [
            'Section',
        ],
        'RoleList' => [
            'Role',
        ],
        'PolicyList' => [
            'Policy',
        ],
        'LocationList' => [
            'Location',
        ],
        'ContentObjectStates' => [
            'ObjectState',
        ],
        'FieldDefinitions' => [
            'FieldDefinition',
        ],
        'UserList' => [
            'User',
        ],
        'names' => [
            'value',
        ],
        'descriptions' => [
            'value',
        ],
        'fields' => [
            'field',
        ],
        'limitations' => [
            'limitation',
        ],
        'values' => [
            'ref',
        ],
    ];

    /**
     * @var list<string>
     */
    protected array $fieldTypeHashElements = [
        'fieldValue',
        'defaultValue',
        'fieldSettings',
        'validatorConfiguration',
    ];

    /**
     * Converts the given string to an array structure.
     */
    public function convert(string $string): array|string|int|bool|float|null
    {
        $oldXmlErrorHandling = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new \DOMDocument();
        $dom->loadXml($string);

        $errors = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors($oldXmlErrorHandling);

        if ($errors) {
            $message = "Detected errors in input XML:\n";
            foreach ($errors as $error) {
                $message .= sprintf(
                    " - In line %d character %d: %s\n",
                    $error->line,
                    $error->column,
                    $error->message
                );
            }
            $message .= "\nIn XML: \n\n" . $string;

            throw new Exceptions\Parser($message);
        }

        return $this->convertDom($dom);
    }

    /**
     * Converts DOM nodes to array structures.
     */
    protected function convertDom(DOMNode $node): array|string|int|bool|float|null
    {
        $isArray = false;
        $current = [];
        $text = '';

        if ($node instanceof DOMElement && $node->hasAttributes()) {
            foreach ($node->attributes as $name => $attribute) {
                $current["_{$name}"] = $attribute->value;
            }
        }

        $parentTagName = $node instanceof DOMElement ? $node->tagName : false;
        foreach ($node->childNodes as $childNode) {
            switch ($childNode->nodeType) {
                case XML_ELEMENT_NODE:
                    $tagName = $childNode->tagName;

                    if (in_array($tagName, $this->fieldTypeHashElements)) {
                        $current[$tagName] = $this->parseFieldTypeHash($childNode);
                    } elseif (!isset($current[$tagName])) {
                        if (isset($this->forceList[$parentTagName]) &&
                             in_array($tagName, $this->forceList[$parentTagName], true)) {
                            $isArray = true;
                            $current[$tagName] = [
                                $this->convertDom($childNode),
                            ];
                        } else {
                            $current[$tagName] = $this->convertDom($childNode);
                        }
                    } elseif (!$isArray) {
                        $current[$tagName] = [
                            $current[$tagName],
                            $this->convertDom($childNode),
                        ];
                        $isArray = true;
                    } else {
                        $current[$tagName][] = $this->convertDom($childNode);
                    }

                    break;

                case XML_TEXT_NODE:
                    $text .= $childNode->wholeText;
                    break;

                case XML_CDATA_SECTION_NODE:
                    $text .= $childNode->data;
                    break;
            }
        }

        $text = trim($text);

        if ($text !== '' && count($current)) {
            $current['#text'] = $text;
        } elseif ($text !== '') {
            $current = $text;
        } elseif (!count($current)) {
            return null;
        }

        return $current;
    }

    protected function parseFieldTypeHash(DOMElement $domElement): array|string|int|bool|float|null
    {
        $result = $this->parseFieldTypeValues($domElement->childNodes);

        if (is_array($result) && empty($result)) {
            // No child values means null
            return null;
        }

        return $result;
    }

    /**
     * Parses a node list of <value> elements.
     */
    protected function parseFieldTypeValues(DOMNodeList $valueNodes): array|string|int|bool|float
    {
        $resultValues = [];
        $resultString = '';

        foreach ($valueNodes as $valueNode) {
            switch ($valueNode->nodeType) {
                case XML_ELEMENT_NODE:
                    if ($valueNode->tagName !== 'value') {
                        throw new \RuntimeException(
                            sprintf(
                                'Invalid value tag: <%s>.',
                                $valueNode->tagName
                            )
                        );
                    }

                    $parsedValue = $this->parseFieldTypeValues($valueNode->childNodes);
                    if ($valueNode->hasAttribute('key')) {
                        $resultValues[$valueNode->getAttribute('key')] = $parsedValue;
                    } else {
                        $resultValues[] = $parsedValue;
                    }
                    break;

                case XML_TEXT_NODE:
                    $resultString .= $valueNode->wholeText;
                    break;

                case XML_CDATA_SECTION_NODE:
                    $resultString .= $valueNode->data;
                    break;
            }
        }

        $resultString = trim($resultString);
        if ($resultString !== '') {
            return $this->castScalarValue($resultString);
        }

        return $resultValues;
    }

    /**
     * Attempts to cast the given $stringValue into a sensible scalar type.
     */
    protected function castScalarValue(string $stringValue): string|int|bool|float
    {
        switch (true) {
            case ctype_digit($stringValue):
                return (int)$stringValue;

            case preg_match('(^[0-9\.]+$)', $stringValue) === 1:
                return (float)$stringValue;

            case strtolower($stringValue) === 'true':
                return true;

            case strtolower($stringValue) === 'false':
                return false;
        }

        return $stringValue;
    }
}
