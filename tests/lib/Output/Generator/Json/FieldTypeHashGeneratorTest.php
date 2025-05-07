<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output\Generator\Json;

use Ibexa\Rest\Output\Generator\Json;
use Ibexa\Rest\Output\Generator\Json\FieldTypeHashGenerator;
use Ibexa\Tests\Rest\Output\Generator\FieldTypeHashGeneratorBaseTest;

class FieldTypeHashGeneratorTest extends FieldTypeHashGeneratorBaseTest
{
    protected function initializeFieldTypeHashGenerator(): FieldTypeHashGenerator
    {
        return new FieldTypeHashGenerator($this->getNormalizer(), $this->getLogger());
    }

    protected function initializeGenerator(): Json
    {
        $fieldTypeGenerator = $this->getFieldTypeHashGenerator();

        self::assertInstanceOf(
            FieldTypeHashGenerator::class,
            $fieldTypeGenerator
        );

        return new Json(
            $fieldTypeGenerator
        );
    }
}
