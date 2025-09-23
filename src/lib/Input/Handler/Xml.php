<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Input\Handler;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\Handler;
use InvalidArgumentException;

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
     *
     * @var array
     */
    protected $forceList = [
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

    protected $fieldTypeHashElements = [
        'fieldValue',
        'defaultValue',
        'fieldSettings',
        'validatorConfiguration',
    ];

    public function convert($string)
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
     *
     * @return array<mixed>|string|null
     */
    protected function convertDom(\DOMNode $node)
    {
        $isArray = false;
        $current = [];
        $text = '';

        if ($node instanceof \DOMElement && $node->attributes !== null) {
            foreach ($node->attributes as $name => $attribute) {
                $current["_{$name}"] = $attribute->value;
            }
        }

        $parentTagName = $node instanceof \DOMElement ? $node->tagName : false;
        foreach ($node->childNodes as $childNode) {
            switch ($childNode->nodeType) {
                case XML_ELEMENT_NODE:
                    $tagName = $childNode->tagName;

                    if (in_array($tagName, $this->fieldTypeHashElements, true)) {
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
                    } elseif (!is_string($current[$tagName])) {
                        if (!is_array($current[$tagName])) {
                            throw new InvalidArgumentException('Current tag name is not an array as expected.');
                        }
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

    /**
     * @param \DOMElement $domElement
     *
     * @return array|string|null
     */
    protected function parseFieldTypeHash(\DOMElement $domElement)
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
     *
     * @param \DOMNodeList $valueNodes
     *
     * @return array|string
     */
    protected function parseFieldTypeValues(\DOMNodeList $valueNodes)
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
     *
     * @param string $stringValue
     *
     * @return mixed
     */
    protected function castScalarValue($stringValue)
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

class_alias(Xml::class, 'EzSystems\EzPlatformRest\Input\Handler\Xml');
