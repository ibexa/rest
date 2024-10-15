<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class VisitorAdapterNormalizer implements NormalizerInterface, NormalizerAwareInterface, ContextAwareNormalizerInterface
{
    use NormalizerAwareTrait;

    private const string CALLED_CONTEXT = __CLASS__ . '_CALLED';

    public const string ENCODER_CONTEXT = 'ENCODER_CONTEXT';

    public const string OUTER_ELEMENT = 'outer_element';

    public function __construct(
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

        if (!$this->normalizer instanceof ContextAwareNormalizerInterface) {
            throw new LogicException(
                sprintf(
                    'Normalizer "%s" must be an instance of "%s".',
                    $this->normalizer::class,
                    ContextAwareNormalizerInterface::class,
                ),
            );
        }

        return $this->normalizer->supportsNormalization(
            $data,
            null,
            $context + [self::CALLED_CONTEXT => true],
        );
    }

    /**
     * @param array<mixed> $context
     *
     * @return array<mixed>
     */
    private function visitValueObject(
        object $object,
        ValueObjectVisitor $valueObjectVisitor,
        ?string $format,
        array $context,
    ): array {
        if (!isset($context['visitor'])) {
            throw new LogicException('Context must have the "Visitor" instance passed.');
        }

        $visitor = $context['visitor'];
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
            $context += [self::OUTER_ELEMENT => true];
        }

        return $context;
    }
}
