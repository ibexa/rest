<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Input;

use Ibexa\Contracts\Rest\Event\BeforeParseEvent;
use Ibexa\Contracts\Rest\Event\ParseEvent;
use Ibexa\Contracts\Rest\Exceptions;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Parsing dispatcher.
 */
class ParsingDispatcher
{
    /**
     * Array of parsers.
     *
     * Structure:
     *
     * <code>
     *  array(
     *      <contentType> => array(
     *          <version> => <parser>,
     *          …
     *      }
     *  )
     * </code>
     *
     * @var array<string, array<string, \Ibexa\Contracts\Rest\Input\Parser>>
     */
    protected array $parsers = [];

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * Construct from optional parsers array.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, array $parsers = [])
    {
        $this->eventDispatcher = $eventDispatcher;

        foreach ($parsers as $mediaType => $parser) {
            $this->addParser($mediaType, $parser);
        }
    }

    /**
     * Adds another parser for the given content type.
     *
     * @param string $mediaType
     * @param \Ibexa\Contracts\Rest\Input\Parser $parser
     */
    public function addParser(string $mediaType, Parser $parser): void
    {
        [$mediaType, $version] = $this->parseMediaTypeVersion($mediaType);

        $this->parsers[$mediaType][$version] = $parser;
    }

    /**
     * Dispatches parsing the given $data according to $mediaType.
     */
    public function parse(array $data, string $mediaType)
    {
        $eventData = [
            $data,
            $mediaType,
        ];

        $beforeEvent = new BeforeParseEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getValueObject();
        }

        $data = $beforeEvent->getData();
        $mediaType = $beforeEvent->getMediaType();

        $valueObject = $beforeEvent->hasValueObject()
            ? $beforeEvent->getValueObject()
            : $this->internalParse($data, $mediaType);

        $this->eventDispatcher->dispatch(
            new ParseEvent($valueObject, ...$eventData)
        );

        return $valueObject;
    }

    /**
     * Parses the given $data according to $mediaType.
     */
    protected function internalParse(array $data, string $mediaType)
    {
        list($mediaType, $version) = $this->parseMediaTypeVersion($mediaType);

        // Remove encoding type
        if (($plusPos = strrpos($mediaType, '+')) !== false) {
            $mediaType = substr($mediaType, 0, $plusPos);
        }

        if (!isset($this->parsers[$mediaType][$version])) {
            throw new Exceptions\Parser("Unknown content type specification: '$mediaType (version: $version)'.");
        }

        return $this->parsers[$mediaType][$version]->parse($data, $this);
    }

    /**
     * Parses and returns the version from a MediaType.
     *
     * @param string $mediaType Ex: text/html; version=1.1
     *
     * @return array{string, string} An array with the media-type string, stripped from the version, and the version (1.0 by default)
     */
    protected function parseMediaTypeVersion(string $mediaType): array
    {
        $version = '1.0';
        $contentType = explode('; ', $mediaType);
        if (count($contentType) > 1) {
            $mediaType = $contentType[0];
            foreach (array_slice($contentType, 1) as $parameterString) {
                if (strpos($contentType[1], '=') === false) {
                    throw new Exceptions\Parser("Unknown parameter format: '$parameterString'");
                }
                list($parameterName, $parameterValue) = explode('=', $parameterString);
                if (trim($parameterName) === 'version') {
                    $version = trim($parameterValue);
                    break;
                }
            }
        }

        return [$mediaType, $version];
    }
}
