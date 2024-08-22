<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

use Ibexa\Rest\Output\Generator\Json;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AdapterNormalizer implements NormalizerInterface, NormalizerAwareInterface, ContextAwareNormalizerInterface
{
    use NormalizerAwareTrait;

    private const string CALLED_CONTEXT = __CLASS__ . '_CALLED';

    /**
     * @var array<class-string, ValueObjectVisitor>
     */
    private array $visitors;

    public function __construct(
        private readonly EncoderInterface $encoder,
    ) {
    }

    /**
     * @param class-string $visitedClassName
     */
    public function addVisitor(string $visitedClassName, ValueObjectVisitor $visitor): void
    {
        $this->visitors[$visitedClassName] = $visitor;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $eligibleVisitor = $this->getEligibleVisitor(is_object($object) ? $object::class : null);
        if ($eligibleVisitor instanceof ValueObjectVisitor) {
            return $this->visitValueObject($object, $eligibleVisitor);
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

        $className = is_object($data) ? $data::class : null;
        $eligibleVisitor = $this->getEligibleVisitor($className);

        if ($eligibleVisitor instanceof ValueObjectVisitor) {
            return true;
        }

        return $this->normalizer->supportsNormalization(
            $data,
            null,
            $context + [self::CALLED_CONTEXT => true],
        );
    }

    /**
     * @return array<mixed>
     */
    private function visitValueObject(object $object, ValueObjectVisitor $valueObjectVisitor): array
    {
        $visitor = $this->createVisitor();
        $generator = $visitor->getGenerator();

        $generator->reset();
        $generator->startDocument($object);

        $valueObjectVisitor->visit($visitor, $generator, $object);

        $generator->endDocument($object);

        return $generator->toArray();
    }

    /**
     * @param class-string|null $className
     */
    private function getEligibleVisitor(?string $className): ?ValueObjectVisitor
    {
        if ($className === null) {
            return null;
        }

        do {
            if (isset($this->visitors[$className])) {
                return $this->visitors[$className];
            }
        } while ($className = get_parent_class($className));

        return null;
    }

    private function createVisitor(): Visitor
    {
        $fieldTypeHashGenerator = new Json\FieldTypeHashGenerator($this->normalizer);
        $valueObjectVisitorDispatcher = new ValueObjectVisitorDispatcher();

        $generator = new Json(
            $fieldTypeHashGenerator,
            $this->encoder,
        );

        $visitor = new Visitor(
            $generator,
            $this->normalizer,
            $this->encoder,
            $valueObjectVisitorDispatcher,
        );

        $valueObjectVisitorDispatcher->setVisitors($this->visitors);
        $valueObjectVisitorDispatcher->setOutputVisitor($visitor);
        $valueObjectVisitorDispatcher->setOutputGenerator($generator);

        return $visitor;
    }
}
