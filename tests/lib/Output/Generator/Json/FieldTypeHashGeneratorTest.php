<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Output\Generator\Json;

use Ibexa\Rest\Output\Generator\Json;
use Ibexa\Rest\Output\Generator\Json\FieldTypeHashGenerator;
use Ibexa\Tests\Rest\Output\Generator\FieldTypeHashGeneratorBaseTest;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FieldTypeHashGeneratorTest extends FieldTypeHashGeneratorBaseTest
{
    /**
     * Initializes the field type hash generator.
     */
    protected function initializeFieldTypeHashGenerator(): FieldTypeHashGenerator
    {
        return new FieldTypeHashGenerator($this->getNormalizer());
    }

    /**
     * Initializes the generator.
     */
    protected function initializeGenerator(): Json
    {
        return new Json(
            $this->getFieldTypeHashGenerator()
        );
    }
}
