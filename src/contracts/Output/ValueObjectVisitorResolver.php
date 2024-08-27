<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Output;

final class ValueObjectVisitorResolver implements ValueObjectVisitorResolverInterface
{
    /**
     * @var array<class-string, ValueObjectVisitor>
     */
    private array $visitors;

    /**
     * @param class-string $visitedClassName
     */
    public function addVisitor(string $visitedClassName, ValueObjectVisitor $visitor): void
    {
        $this->visitors[$visitedClassName] = $visitor;
    }

    public function resolveValueObjectVisitor(object $object): ?ValueObjectVisitor
    {
        $className = $object::class;

        do {
            if (isset($this->visitors[$className])) {
                return $this->visitors[$className];
            }
        } while ($className = get_parent_class($className));

        return null;
    }
}
