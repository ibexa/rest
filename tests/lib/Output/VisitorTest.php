<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitorResolverInterface;
use Ibexa\Contracts\Rest\Output\Visitor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VisitorTest extends TestCase
{
    public function testVisitDocument(): void
    {
        //TODO refactor
    }

    public function testVisitEmptyDocument(): void
    {
        //TODO refactor
    }

    public function testVisitValueObject(): void
    {
        //TODO refactor
    }

    public function testSetHeaders(): void
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    /**
     * @todo This is a test for a feature that needs refactoring.
     *
     * @see \Ibexa\Contracts\Rest\Output\Visitor::visit
     */
    public function testSetFilteredHeaders()
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Accept-Patch', false);
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/xml',
                    'Accept-Patch' => [0 => ''],
                ]
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeadersNoOverwrite(): void
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Content-Type', 'text/html');
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/xml',
                ]
            ),
            $visitor->visit($data)
        );
    }

    public function testSetHeaderResetAfterVisit(): void
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setHeader('Content-Type', 'text/xml');

        $visitor->visit($data);
        $result = $visitor->visit($data);

        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                []
            ),
            $result
        );
    }

    public function testSetStatusCode(): void
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setStatus(201);
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_CREATED
            ),
            $visitor->visit($data)
        );
    }

    public function testSetStatusCodeNoOverride(): void
    {
        $data = new stdClass();

        $visitor = $this->getVisitorMock();

        $visitor->setStatus(201);
        $visitor->setStatus(404);

        self::assertEquals(
            new Response(
                null,
                Response::HTTP_CREATED
            ),
            $visitor->visit($data)
        );
    }

    public function getValueObjectVisitorResolverMock(): ValueObjectVisitorResolverInterface&MockObject
    {
        return $this->createMock(ValueObjectVisitorResolverInterface::class);
    }

    protected function getGeneratorMock(): Generator&MockObject
    {
        return $this->createMock(Generator::class);
    }

    protected function getNormalizerMock(): NormalizerInterface&MockObject
    {
        return $this->createMock(NormalizerInterface::class);
    }

    protected function getEncoderMock(): EncoderInterface&MockObject
    {
        return $this->createMock(EncoderInterface::class);
    }

    protected function getVisitorMock(): Visitor&MockObject
    {
        return $this->getMockBuilder(Visitor::class)
            ->setMethods(['visitValueObject'])
            ->setConstructorArgs(
                [
                    $this->getGeneratorMock(),
                    $this->getNormalizerMock(),
                    $this->getEncoderMock(),
                    $this->getValueObjectVisitorResolverMock(),
                ],
            )
            ->getMock();
    }
}
