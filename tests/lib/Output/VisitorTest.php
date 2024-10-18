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
    private Visitor $visitor;

    private NormalizerInterface&MockObject $normalizer;

    private EncoderInterface&MockObject $encoder;

    private Generator&MockObject $generator;

    private ValueObjectVisitorResolverInterface&MockObject $valueObjectVisitorResolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->generator = $this->createMock(Generator::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->encoder = $this->createMock(EncoderInterface::class);
        $this->valueObjectVisitorResolver = $this->createMock(ValueObjectVisitorResolverInterface::class);

        $this->visitor = new Visitor(
            $this->generator,
            $this->normalizer,
            $this->encoder,
            $this->valueObjectVisitorResolver,
            'json',
        );
    }

    public function testVisitDocument(): void
    {
        $data = new stdClass();
        $content = 'Hello world!';

        $this->normalizer
            ->expects(self::once())
            ->method('normalize')
            ->with($data, 'json', ['visitor' => $this->visitor])
            ->willReturn($content);

        $this->encoder
            ->expects(self::once())
            ->method('encode')
            ->with($content)
            ->willReturn($content);

        self::assertEquals(
            new Response($content, Response::HTTP_OK, []),
            $this->visitor->visit($data),
        );
    }

    public function testVisitEmptyDocument(): void
    {
        $data = new stdClass();

        $this->normalizer
            ->expects(self::once())
            ->method('normalize')
            ->with($data, 'json', ['visitor' => $this->visitor])
            ->willReturn(null);

        $this->encoder
            ->expects(self::once())
            ->method('encode')
            ->with(null)
            ->willReturn(null);

        self::assertEquals(
            new Response(null, Response::HTTP_OK, []),
            $this->visitor->visit($data),
        );
    }

    public function testSetHeaders(): void
    {
        $data = new stdClass();

        $visitor = $this->visitor;

        $visitor->setHeader('Content-Type', 'text/xml');
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/xml',
                ],
            ),
            $visitor->visit($data),
        );
    }

    /**
     * @todo This is a test for a feature that needs refactoring.
     */
    public function testSetFilteredHeaders(): void
    {
        $data = new stdClass();

        $visitor = $this->visitor;

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
            $visitor->visit($data),
        );
    }

    public function testSetHeadersNoOverwrite(): void
    {
        $data = new stdClass();

        $visitor = $this->visitor;

        $visitor->setHeader('Content-Type', 'text/xml');
        $visitor->setHeader('Content-Type', 'text/html');
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/xml',
                ],
            ),
            $visitor->visit($data),
        );
    }

    public function testSetHeaderResetAfterVisit(): void
    {
        $data = new stdClass();

        $visitor = $this->visitor;

        $visitor->setHeader('Content-Type', 'text/xml');

        $visitor->visit($data);
        $result = $visitor->visit($data);

        self::assertEquals(
            new Response(
                null,
                Response::HTTP_OK,
                [],
            ),
            $result,
        );
    }

    public function testSetStatusCode(): void
    {
        $data = new stdClass();

        $visitor = $this->visitor;

        $visitor->setStatus(201);
        self::assertEquals(
            new Response(
                null,
                Response::HTTP_CREATED,
            ),
            $visitor->visit($data),
        );
    }

    public function testSetStatusCodeNoOverride(): void
    {
        $data = new stdClass();

        $visitor = $this->visitor;

        $visitor->setStatus(201);
        $visitor->setStatus(404);

        self::assertEquals(
            new Response(
                null,
                Response::HTTP_CREATED,
            ),
            $visitor->visit($data),
        );
    }
}
