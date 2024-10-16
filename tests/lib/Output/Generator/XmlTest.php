<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Rest\Output\Generator;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\Output\Generator\InMemory;
use Ibexa\Tests\Rest\Output\GeneratorTest;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

require_once __DIR__ . '/../GeneratorTest.php';

/**
 * Xml generator test class.
 */
final class XmlTest extends GeneratorTest
{
    public function testGeneratorDocument(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $response = $generator->endDocument('test');

        self::assertSame(
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
            $response,
        );
    }

    public function testGeneratorElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startObjectElement('element');
        $generator->endObjectElement('element');

        self::assertSame(
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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

        $response = $generator->endDocument('test');

        self::assertSame(
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
            $response,
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
            $generator->endDocument('test'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
            $generator->endDocument('test')
        );
    }

    public function testGeneratorHashElement(): void
    {
        $generator = $this->getGenerator();

        $generator->startDocument('test');

        $generator->startHashElement('elements');
        $generator->startList('element');

        $generator->startValueElement('element', 'element value 1', ['attribute' => 'attribute value 1']);
        $generator->endValueElement('element');

        $generator->startValueElement('element', 'element value 2', ['attribute' => 'attribute value 2']);
        $generator->endValueElement('element');

        $generator->endList('element');
        $generator->endHashElement('elements');

        self::assertSame(
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
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
            file_get_contents(__DIR__ . '/_fixtures/' . __FUNCTION__ . '.xml'),
            $generator->endDocument('test')
        );
    }

    public function assertSnapshot(string $snapshotName, string $generatedContent): void
    {
        self::assertXmlStringEqualsXmlFile(
            sprintf('%s/_fixtures/%s.xml', __DIR__, $snapshotName),
            $generatedContent
        );
    }

    public function testGetMediaType(): void
    {
        $generator = $this->getGenerator();

        self::assertEquals(
            'application/vnd.ibexa.api.Section+xml',
            $generator->getMediaType('Section')
        );
    }

    public function testSerializeBool(): void
    {
        $generator = $this->getGenerator();

        self::assertTrue($generator->serializeBool(true) === 'true');
        self::assertTrue($generator->serializeBool(false) === 'false');
        self::assertTrue($generator->serializeBool('notbooleanbuttrue') === 'true');
    }

    protected function getGenerator(): Generator
    {
        if (!isset($this->generator)) {
            $fieldTypeHashGenerator = new InMemory\Xml\FieldTypeHashGenerator(
                $this->createMock(NormalizerInterface::class),
            );
            $this->generator = new InMemory\Xml(
                $fieldTypeHashGenerator,
            );
        }
        $this->generator->setFormatOutput(true);

        return $this->generator;
    }
}
