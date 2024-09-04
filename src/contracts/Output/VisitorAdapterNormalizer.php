<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use Ibexa\Rest\Output\Generator\InMemory\Xml as InMemoryXml;
use Ibexa\Rest\Output\Generator\Json;
use LogicException;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VisitorAdapterNormalizer implements NormalizerInterface, NormalizerAwareInterface, ContextAwareNormalizerInterface
{
    use NormalizerAwareTrait;

    private const string CALLED_CONTEXT = __CLASS__ . '_CALLED';

    public function __construct(
        private readonly EncoderInterface $encoder,
        private readonly ValueObjectVisitorResolverInterface $valueObjectVisitorResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $eligibleVisitor = is_object($object)
            ? $this->valueObjectVisitorResolver->resolveValueObjectVisitor($object)
            : null;

        if ($eligibleVisitor instanceof ValueObjectVisitor) {
            return $this->visitValueObject($object, $eligibleVisitor, $format);
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

        if (!$this->normalizer instanceof ContextAwareNormalizerInterface) {
            throw new LogicException(
                sprintf(
                    'Normalizer "%s" must be an instance of "%s".',
                    $this->normalizer::class,
                    ContextAwareNormalizerInterface::class,
                )
            );
        }

        return $this->normalizer->supportsNormalization(
            $data,
            null,
            $context + [self::CALLED_CONTEXT => true],
        );
    }

    /**
     * @return array<array<mixed>, array<mixed>>
     */
    private function visitValueObject(
        object $object,
        ValueObjectVisitor $valueObjectVisitor,
        string $format
    ): array {
        $visitor = $this->createVisitor($format);
        $generator = $visitor->getGenerator();

        $generator->reset();
        $generator->startDocument($object);

        $valueObjectVisitor->visit($visitor, $generator, $object);

        $generator->endDocument($object);

        $normalizedData = $generator->toArray();
        $encoderContext = $generator->getEncoderContext($normalizedData);

        return [$generator->transformData($normalizedData), $encoderContext];
    }

    private function createVisitor(string $format): Visitor
    {
        $fieldTypeHashGenerator = new Json\FieldTypeHashGenerator($this->normalizer);

        $generator = $format === 'xml'
            ? new InMemoryXml($fieldTypeHashGenerator)
            : new Json($fieldTypeHashGenerator);

        return new Visitor(
            $generator,
            $this->normalizer,
            $this->encoder,
            $this->valueObjectVisitorResolver,
            $format,
        );
    }
}
