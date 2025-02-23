<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use Ibexa\Contracts\Rest\Output\Generator as BaseGenerator;
use Ibexa\Rest\Output\Generator;
use LogicException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VisitorAdapterNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string CALLED_CONTEXT = __CLASS__ . '_CALLED';

    public const string ENCODER_CONTEXT = 'ENCODER_CONTEXT';

    public function __construct(
        private readonly EncoderInterface $jsonEncoder,
        private readonly EncoderInterface $xmlEncoder,
        private readonly ValueObjectVisitorResolverInterface $valueObjectVisitorResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|bool|string|int|float|null|\ArrayObject
    {
        $eligibleVisitor = is_object($object)
            ? $this->valueObjectVisitorResolver->resolveValueObjectVisitor($object)
            : null;

        if ($eligibleVisitor instanceof ValueObjectVisitor) {
            return $this->visitValueObject($object, $eligibleVisitor, $format, $context);
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (($context[self::CALLED_CONTEXT] ?? false) === true) {
            return false;
        }

        $eligibleVisitor = is_object($data)
            ? $this->valueObjectVisitorResolver->resolveValueObjectVisitor($data)
            : null;

        if ($eligibleVisitor instanceof ValueObjectVisitor) {
            return true;
        }

        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new LogicException(
                sprintf(
                    'Normalizer "%s" must be an instance of "%s".',
                    $this->normalizer::class,
                    NormalizerInterface::class,
                ),
            );
        }

        return $this->normalizer->supportsNormalization(
            $data,
            $format,
            $context + [self::CALLED_CONTEXT => true],
        );
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
     *
     * @throws \LogicException
     */
    private function visitValueObject(
        object $object,
        ValueObjectVisitor $valueObjectVisitor,
        ?string $format,
        array $context,
    ): array {
        $visitor = $context['visitor'] ?? $this->createVisitor($format);
        $generator = $visitor->getGenerator();

        $generator->reset();
        $generator->startDocument($object);
        $valueObjectVisitor->visit($visitor, $generator, $object);
        $generator->endDocument($object);

        $data = $generator->getData();

        $normalizedData = $this->normalizer->normalize(
            $data,
            $format,
            $this->buildContext($context, $format),
        );

        return $normalizedData + [self::ENCODER_CONTEXT => $generator->getEncoderContext(get_object_vars($data))];
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    private function buildContext(array $context, ?string $format): array
    {
        $context += [self::CALLED_CONTEXT => true];

        if ($format === 'xml') {
            $context += [Generator\Xml::OUTER_ELEMENT => true];
        }

        return $context;
    }

    /**
     * @throws \LogicException
     */
    private function createGenerator(string $format): BaseGenerator
    {
        if ($format === 'xml') {
            return new Generator\Xml(
                new Generator\Xml\FieldTypeHashGenerator($this->normalizer),
            );
        }

        if ($format === 'json') {
            return new Generator\Json(
                new Generator\Json\FieldTypeHashGenerator($this->normalizer),
            );
        }

        throw new LogicException(
            sprintf('%s format is not supported by %s.', $format, self::class),
        );
    }

    /**
     * @throws \LogicException
     */
    private function createVisitor(?string $format): Visitor
    {
        $format ??= 'json';

        $generator = $this->createGenerator($format);

        return new Visitor(
            $generator,
            $this->normalizer,
            $format === 'xml' ? $this->xmlEncoder : $this->jsonEncoder,
            $this->valueObjectVisitorResolver,
            $format,
        );
    }
}
