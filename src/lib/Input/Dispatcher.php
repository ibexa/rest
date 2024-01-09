<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Input;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\Handler;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Message;

/**
 * Input dispatcher.
 */
class Dispatcher
{
    /**
     * Array of handlers.
     *
     * Structure:
     *
     * <code>
     *  array(
     *      <type> => <handler>,
     *      …
     *  )
     * </code>
     *
     * @var \Ibexa\Contracts\Rest\Input\Handler[]
     */
    protected $handlers = [];

    /**
     * @var \Ibexa\Contracts\Rest\Input\ParsingDispatcher
     */
    protected $parsingDispatcher;

    /**
     * Construct from optional parsers array.
     *
     * @param \Ibexa\Contracts\Rest\Input\ParsingDispatcher $parsingDispatcher
     * @param \Ibexa\Contracts\Rest\Input\Handler[] $handlers
     */
    public function __construct(ParsingDispatcher $parsingDispatcher, array $handlers = [])
    {
        $this->parsingDispatcher = $parsingDispatcher;
        foreach ($handlers as $type => $handler) {
            $this->addHandler($type, $handler);
        }
    }

    /**
     * Adds another handler for the given content type.
     *
     * @param string $type
     * @param \Ibexa\Contracts\Rest\Input\Handler $handler
     */
    public function addHandler($type, Handler $handler)
    {
        $this->handlers[$type] = $handler;
    }

    /**
     * Parse provided request.
     *
     * @param \Ibexa\Rest\Message $message
     *
     * @return mixed
     */
    public function parse(Message $message)
    {
        if (!isset($message->headers['Content-Type'])) {
            throw new Exceptions\Parser('Missing Content-Type header in message.');
        }

        $mediaTypeParts = explode(';', $message->headers['Content-Type']);
        $contentTypeParts = explode('+', $mediaTypeParts[0]);
        if (count($contentTypeParts) !== 2) {
            // TODO expose default format
            $contentTypeParts[1] = 'xml';
        }

        $media = $contentTypeParts[0];

        // add version if given
        if (count($mediaTypeParts) > 1) {
            $parameters = $this->parseParameters(implode(';', array_slice($mediaTypeParts, 1)));
            if (isset($parameters['version'])) {
                $media .= '; version=' . $parameters['version'];
            }
        }

        $format = $contentTypeParts[1];

        if (!isset($this->handlers[$format])) {
            throw new Exceptions\Parser("Unknown format specification: '{$format}'.");
        }

        $rawArray = $this->handlers[$format]->convert($message->body);

        // Only 1 XML root node
        $rootNodeArray = reset($rawArray);

        // @todo: This needs to be refactored in order to make the called URL
        // available to parsers in the server in a sane way
        if (isset($message->headers['Url'])) {
            $rootNodeArray['__url'] = $message->headers['Url'];
        }
        if (isset($message->headers['__publish'])) {
            $rootNodeArray['__publish'] = $message->headers['__publish'];
        }

        return $this->parsingDispatcher->parse($rootNodeArray, $media);
    }

    private function parseParameters($mediaTypePart)
    {
        $parameters = [];
        foreach (explode(';', $mediaTypePart) as $parameterString) {
            list($parameterName, $parameterValue) = explode('=', $parameterString);
            $parameters[trim($parameterName)] = trim($parameterValue);
        }

        return $parameters;
    }
}

class_alias(Dispatcher::class, 'EzSystems\EzPlatformRest\Input\Dispatcher');
