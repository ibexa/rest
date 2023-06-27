<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Tests\Rest\Output;

use Ibexa\Contracts\Rest\Output\Exceptions\OutputGeneratorException;
use PHPUnit\Framework\TestCase;

/**
 * Output generator test class.
 */
abstract class GeneratorTest extends TestCase
{
    /**
     * @var \Ibexa\Contracts\Rest\Output\Generator
     */
    protected $generator;

    /**
     * @return \Ibexa\Contracts\Rest\Output\Generator
     */
    abstract protected function getGenerator();

    public function testInvalidDocumentStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startDocument('test');
    }

    public function testValidDocumentStartAfterReset()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->reset();
        $generator->startDocument('test');

        $this->assertNotNull($generator->endDocument('test'));
    }

    public function testInvalidDocumentNameEnd()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->endDocument('invalid');
    }

    public function testInvalidOuterElementStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startObjectElement('element');
    }

    public function testInvalidElementEnd()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->endObjectElement('invalid');
    }

    public function testInvalidDocumentEnd()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->endDocument('test');
    }

    public function testInvalidAttributeOuterStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startAttribute('attribute', 'value');
    }

    public function testInvalidAttributeDocumentStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startAttribute('attribute', 'value');
    }

    public function testInvalidAttributeListStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->startList('list');
        $generator->startAttribute('attribute', 'value');
    }

    public function testInvalidValueElementOuterStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startValueElement('element', 'value');
    }

    public function testInvalidValueElementDocumentStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startValueElement('element', 'value');
    }

    public function testInvalidListOuterStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startList('list');
    }

    public function testInvalidListDocumentStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startList('list');
    }

    public function testInvalidListListStart()
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->startList('list');
        $generator->startList('attribute', 'value');
    }

    public function testEmptyDocument()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $this->assertTrue($generator->isEmpty());
    }

    public function testNonEmptyDocument()
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');

        $this->assertFalse($generator->isEmpty());
    }

    abstract protected function assertSnapshot(string $snapshotName, string $generatedContent): void;

    /**
     * @dataProvider getDataForTestStartValueElementWithAttributes
     *
     * @phpstan-param scalar|null $elementValue
     * @phpstan-param array<string, scalar|null> $attributes
     */
    public function testStartValueElementWithAttributes($elementValue, array $attributes): void
    {
        $generator = $this->getGenerator();
        $generator->startDocument('test');
        $generator->startObjectElement('Element');
        $generator->startValueElement(
            'element',
            $elementValue,
            $attributes
        );
        $generator->endValueElement('element');
        $generator->endObjectElement('Element');

        static::assertSnapshot(__FUNCTION__ . '/' . $this->dataName(), $generator->endDocument('test'));
    }

    /**
     * @return iterable<string, array{scalar|null, array<string, scalar|null>}>
     */
    public function getDataForTestStartValueElementWithAttributes(): iterable
    {
        // data set name corresponds to the file names located in
        // ./tests/lib/Output/Generator/_fixtures/testStartValueElementWithAttributes

        yield 'strings' => [
            'value',
            [
                'attribute1' => 'attribute_value1',
                'attribute2' => 'attribute_value2',
            ],
        ];

        yield 'booleans' => [
            false,
            [
                'attribute1' => true,
                'attribute2' => false,
            ],
        ];

        yield 'integers' => [
            1,
            [
                'attribute1' => 2,
                'attribute2' => 3,
            ],
        ];

        yield 'floats' => [
            1.2,
            [
                'attribute1' => 2.3,
                'attribute2' => 3.2,
            ],
        ];

        yield 'null' => [
            null,
            [
                'attribute1' => null,
                'attribute2' => 'foo', // let's see if 1st null affects rendering
            ],
        ];
    }
}

class_alias(GeneratorTest::class, 'EzSystems\EzPlatformRest\Tests\Output\GeneratorTest');
