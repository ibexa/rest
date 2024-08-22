<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Rest\Output\Normalizer\TestData;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Visits a value object into an HTTP Response.
 */
class Visitor
{
    /**
     * HTTP Response Object.
     */
    protected Response $response;

    /**
     * Used to ensure that the status code can't be overwritten.
     */
    private ?int $statusCode = null;

    public function __construct(
        private readonly Generator $generator,
        private readonly NormalizerInterface $normalizer,
        private readonly EncoderInterface $encoder,
        private readonly ValueObjectVisitorDispatcher $valueObjectVisitorDispatcher,
        private readonly ?LocationService $locationService = null, //TODO to remove
    ) {
        $this->response = new Response('', 200);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function visit(mixed $data)
    {
        //TODO to remove
        $data = new TestData();
        $data->setName('test test');
        $location = $this->locationService->loadLocation(2);
        $location = new RestLocation($location, 2);
        $data->setLocation($location);

        $normalizedData = $this->normalizer->normalize($data);

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

        $response->setContent($this->encoder->encode($normalizedData, 'json'));

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
     */
    public function getMediaType(string $type): string
    {
        return $this->generator->getMediaType($type);
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getGenerator(): Generator
    {
        return $this->generator;
    }
}
