<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output\Generator;

use Ibexa\Contracts\Rest\Output\Exceptions\OutputGeneratorException;
use Ibexa\Rest\Output\Generator\Json;
use Ibexa\Rest\Output\Generator\Json\FieldTypeHashGenerator;
use Ibexa\Tests\Rest\Output\GeneratorTest;

require_once __DIR__ . '/../GeneratorTest.php';

/**
 * Json output generator test class.
 */
class JsonTest extends GeneratorTest
{
    protected $generator;

    public function testGeneratorDocument(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        self::assertSame(
            '{}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');
        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorElementMediaTypeOverwrite(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element', 'User');
        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.User+json"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorStackedElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->startObjectElement('stacked');
        $generator->endObjectElement('stacked');

        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","stacked":{"_media-type":"application\/vnd.ibexa.api.stacked+json"}}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorAttribute(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->attribute('attribute', 'value');

        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","_attribute":"value"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorStartEndAttribute(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->startAttribute('attribute', 'value');
        $generator->endAttribute('attribute');

        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","_attribute":"value"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorMultipleAttributes(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->attribute('attribute1', 'value');
        $generator->attribute('attribute2', 'value');

        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","_attribute1":"value","_attribute2":"value"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorValueElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->valueElement('value', '42');

        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","value":"42"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorStartEndValueElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->startValueElement('value', '42');
        $generator->endValueElement('value');

        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","value":"42"}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorElementList(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('elementList');

        $generator->startList('elements');

        $generator->startObjectElement('element');
        $generator->endObjectElement('element');

        $generator->startObjectElement('element');
        $generator->endObjectElement('element');

        $generator->endList('elements');

        $generator->endObjectElement('elementList');

        self::assertSame(
            '{"elementList":{"_media-type":"application\/vnd.ibexa.api.elementList+json","elements":[{"_media-type":"application\/vnd.ibexa.api.element+json"},{"_media-type":"application\/vnd.ibexa.api.element+json"}]}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorHashElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startHashElement('elements');

        $generator->startValueElement('element', 'element value 1', ['attribute' => 'attribute value 1']);
        $generator->endValueElement('element');

        $generator->endHashElement('elements');

        self::assertSame(
            '{"elements":{"element":{"_attribute":"attribute value 1","#text":"element value 1"}}}',
            $generator->endDocument('test')
        );
    }

    public function testGeneratorValueList(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');
        $generator->startObjectElement('element');
        $generator->startList('simpleValue');

        $generator->startValueElement('simpleValue', 'value1');
        $generator->endValueElement('simpleValue');
        $generator->startValueElement('simpleValue', 'value2');
        $generator->endValueElement('simpleValue');

        $generator->endList('simpleValue');
        $generator->endObjectElement('element');

        self::assertSame(
            '{"element":{"_media-type":"application\/vnd.ibexa.api.element+json","simpleValue":["value1","value2"]}}',
            $generator->endDocument('test')
        );
    }

    public function assertSnapshot(string $snapshotName, string $generatedContent): void
    {
        self::assertJsonStringEqualsJsonFile(
            sprintf('%s/_fixtures/%s.json', __DIR__, $snapshotName),
            $generatedContent
        );
    }

    public function testGetMediaType(): void
    {
        $generator = $this->getGenerator();

        self::assertEquals(
            'application/vnd.ibexa.api.Section+json',
            $generator->getMediaType('Section')
        );
    }

    public function testGeneratorMultipleElements(): void
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');
        $generator->endObjectElement('element');

        $generator->startObjectElement('element');
    }

    public function testGeneratorMultipleStackedElements(): void
    {
        $this->expectException(OutputGeneratorException::class);

        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');

        $generator->startObjectElement('stacked');
        $generator->endObjectElement('stacked');

        $generator->startObjectElement('stacked');
    }

    public function testSerializeBool(): void
    {
        $generator = $this->getGenerator();

        self::assertTrue($generator->serializeBool(true) === true);
        self::assertTrue($generator->serializeBool(false) === false);
        self::assertTrue($generator->serializeBool('notbooleanbuttrue') === true);
    }

    protected function getGenerator()
    {
        if (!isset($this->generator)) {
            $this->generator = new Json(
                $this->createMock(FieldTypeHashGenerator::class)
            );
        }

        return $this->generator;
    }
}
