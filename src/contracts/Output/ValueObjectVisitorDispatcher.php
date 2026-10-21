<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Output;

use Error;
use Ibexa\Contracts\Rest\Output\Exceptions\InvalidTypeException;
use Ibexa\Contracts\Rest\Output\Exceptions\NoVisitorFoundException;

/**
 * Dispatches value objects to a visitor depending on the class name.
 */
class ValueObjectVisitorDispatcher
{
    /**
     * @var ValueObjectVisitor[]
     */
    private $visitors;

    /**
     * @var Visitor
     */
    private $outputVisitor;

    /**
     * @var Generator
     */
    private $outputGenerator;

    public function setOutputVisitor(Visitor $outputVisitor)
    {
        $this->outputVisitor = $outputVisitor;
    }

    public function setOutputGenerator(Generator $outputGenerator)
    {
        $this->outputGenerator = $outputGenerator;
    }

    /**
     * @param string $visitedClassName The FQN of the visited class
     * @param ValueObjectVisitor $visitor The visitor object
     */
    public function addVisitor(
        $visitedClassName,
        ValueObjectVisitor $visitor
    ) {
        $this->visitors[$visitedClassName] = $visitor;
    }

    /**
     * @param object $data The visited object
     *
     * @throws NoVisitorFoundException
     * @throws InvalidTypeException
     *
     * @return mixed
     */
    public function visit($data)
    {
        if ($data instanceof Error) {
            // Skip internal PHP errors serialization
            throw $data;
        }

        if (!is_object($data)) {
            throw new InvalidTypeException($data);
        }
        $checkedClassNames = [];

        $className = get_class($data);
        do {
            $checkedClassNames[] = $className;
            if (isset($this->visitors[$className])) {
                return $this->visitors[$className]->visit($this->outputVisitor, $this->outputGenerator, $data);
            }
        } while ($className = get_parent_class($className));

        $interfaces = class_implements($data);
        foreach ($interfaces as $interface) {
            if (isset($this->visitors[$interface])) {
                return $this->visitors[$interface]->visit($this->outputVisitor, $this->outputGenerator, $data);
            }
        }

        throw new NoVisitorFoundException($checkedClassNames);
    }
}

class_alias(ValueObjectVisitorDispatcher::class, 'EzSystems\EzPlatformRest\Output\ValueObjectVisitorDispatcher');
