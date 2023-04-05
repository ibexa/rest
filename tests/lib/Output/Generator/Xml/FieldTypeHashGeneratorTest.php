<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Output\Generator\Xml;

use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Rest\Output\Generator\Xml\FieldTypeHashGenerator;
use Ibexa\Tests\Rest\Output\Generator\FieldTypeHashGeneratorBaseTest;

final class FieldTypeHashGeneratorTest extends FieldTypeHashGeneratorBaseTest
{
    /**
     * Initializes the field type hash generator.
     */
    protected function initializeFieldTypeHashGenerator(): FieldTypeHashGenerator
    {
        return new FieldTypeHashGenerator($this->getNormalizer(), $this->getLogger());
    }

    /**
     * Initializes the generator.
     */
    protected function initializeGenerator(): Xml
    {
        $generator = new Xml(
            $this->getFieldTypeHashGenerator()
        );
        $generator->setFormatOutput(true);

        return $generator;
    }
}
