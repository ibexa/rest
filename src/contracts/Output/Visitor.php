<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Output;

use Symfony\Component\HttpFoundation\Response;

/**
 * Visits a value object into an HTTP Response.
 */
class Visitor
{
    /**
     * @var \Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher
     */
    protected $valueObjectVisitorDispatcher = [];

    /**
     * Generator.
     *
     * @var \Ibexa\Contracts\Rest\Output\Generator
     */
    protected $generator;

    /**
     * HTTP Response Object.
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * Used to ensure that the status code can't be overwritten.
     *
     * @var int
     */
    private $statusCode;

    /**
     * Construct from Generator and an array of concrete view model visitors.
     *
     * @param \Ibexa\Contracts\Rest\Output\Generator $generator
     * @param \Ibexa\Contracts\Rest\Output\ValueObjectVisitorDispatcher $valueObjectVisitorDispatcher
     */
    public function __construct(Generator $generator, ValueObjectVisitorDispatcher $valueObjectVisitorDispatcher)
    {
        $this->generator = $generator;
        $this->valueObjectVisitorDispatcher = $valueObjectVisitorDispatcher;
        $this->response = new Response('', Response::HTTP_OK);
    }

    /**
     * Set HTTP response header.
     *
     * Does not allow overwriting of response headers. The first definition of
     * a header will be used.
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        if (!$this->response->headers->has($name)) {
            $this->response->headers->set($name, $value);
        }
    }

    /**
     * Sets the given status code in the corresponding header.
     *
     * Note that headers are generally not overwritten!
     *
     * @param int $statusCode
     */
    public function setStatus($statusCode)
    {
        if ($this->statusCode === null) {
            $this->statusCode = $statusCode;
            $this->response->setStatusCode($statusCode);
        }
    }

    /**
     * Visit struct returned by controllers.
     *
     * @param mixed $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function visit($data)
    {
        $this->generator->reset();
        $this->generator->startDocument($data);

        $this->visitValueObject($data);

        //@todo Needs refactoring!
        // A hackish solution to enable outer visitors to disable setting
        // of certain headers in inner visitors, for example Accept-Patch header
        // which is valid in GET/POST/PATCH for a resource, but must not appear
        // in the list of resources
        foreach ($this->response->headers->all() as $headerName => $headerValue) {
            if ($headerValue[0] === false) {
                $this->response->headers->remove($headerName);
            }
        }

        $response = clone $this->response;

        $response->setContent($this->generator->isEmpty() ? null : $this->generator->endDocument($data));

        // reset the inner response
        $this->response = new Response(null, Response::HTTP_OK);
        $this->statusCode = null;

        return $response;
    }

    /**
     * Visit struct returned by controllers.
     *
     * Can be called by sub-visitors to visit nested objects.
     *
     * @param object $data
     *
     * @return mixed
     */
    public function visitValueObject($data)
    {
        $this->valueObjectVisitorDispatcher->setOutputGenerator($this->generator);
        $this->valueObjectVisitorDispatcher->setOutputVisitor($this);

        return $this->valueObjectVisitorDispatcher->visit($data);
    }

    /**
     * Generates a media type for $type based on the used generator.
     *
     * @param string $type
     *
     * @see \Ibexa\Rest\Generator::getMediaType()
     *
     * @return string
     */
    public function getMediaType($type)
    {
        return $this->generator->getMediaType($type);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
