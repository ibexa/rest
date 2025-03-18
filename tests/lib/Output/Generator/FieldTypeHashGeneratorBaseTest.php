<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Output\Generator;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class FieldTypeHashGeneratorBaseTest extends TestCase
{
    private $generator;

    private $fieldTypeHashGenerator;

    /** @var \Symfony\Component\Serializer\Normalizer\NormalizerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private NormalizerInterface $normalizer;

    /** @var \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private LoggerInterface $logger;

    private array $iniPrecisions;

    /**
     * To make sure float values are serialized with same precision across php versions we force precision.
     */
    public function setUp(): void
    {
        $this->iniPrecisions = [ini_set('precision', 17), ini_set('serialize_precision', 17)];
    }

    public function tearDown(): void
    {
        ini_set('precision', $this->iniPrecisions[0]);
        ini_set('serialize_precision', $this->iniPrecisions[1]);
    }

    /**
     * Initializes the field type hash generator.
     */
    abstract protected function initializeFieldTypeHashGenerator();

    /**
     * Initializes the generator.
     *
     * @return \Ibexa\Contracts\Rest\Output\Generator
     */
    abstract protected function initializeGenerator();

    /**
     * @return \Symfony\Component\Serializer\Normalizer\NormalizerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    final protected function getNormalizer(): NormalizerInterface
    {
        return $this->normalizer ??= $this->createMock(NormalizerInterface::class);
    }

    /**
     * @return \Psr\Log\LoggerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    final protected function getLogger(): LoggerInterface
    {
        return $this->logger ??= $this->createMock(LoggerInterface::class);
    }

    public function testGenerateNull(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            null
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateBoolValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            true
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateIntegerValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            23
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateFloatValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            23.424242424242424242
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateStringValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            'Sindelfingen'
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateEmptyStringValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            ''
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateStringValueWithSpecialChars(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            '<?xml version="1.0" encoding="UTF-8"?><ezxml>Sindelfingen</ezxml>'
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateListArrayValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            [
                23,
                true,
                'Sindelfingen',
                null,
            ]
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateHashArrayValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            [
                'age' => 23,
                'married' => true,
                'city' => 'Sindelfingen',
                'cause' => null,
            ]
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateHashArrayMixedValue(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            [
                23,
                'married' => true,
                'Sindelfingen',
                'cause' => null,
            ]
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateComplexValueAuthor(): void
    {
        $this->getGenerator()->generateFieldTypeHash(
            'fieldValue',
            [
                ['id' => 1, 'name' => 'Joe Sindelfingen', 'email' => 'sindelfingen@example.com'],
                ['id' => 2, 'name' => 'Joe Bielefeld', 'email' => 'bielefeld@example.com'],
            ]
        );

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateUsingNormalizer(): void
    {
        $object = (object)[];
        $this->getNormalizer()
            ->expects(self::once())
            ->method('normalize')
            ->with(self::identicalTo($object))
            ->willReturn([
                'id' => 1,
                'type' => 'foo',
                'internal_hash' => [
                    'foo' => 'bar',
                ],
                'internal_list' => [
                    42,
                    56,
                ],
            ]);

        $this->getGenerator()->generateFieldTypeHash('fieldValue', $object);

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateObjectUsingNormalizer(): void
    {
        $object = (object)[];
        $this->getNormalizer()
            ->expects(self::once())
            ->method('normalize')
            ->with(self::identicalTo($object))
            ->willReturnArgument(0);

        $this->getGenerator()->generateFieldTypeHash('fieldValue', $object);

        $this->assertSerializationSame(__FUNCTION__);
    }

    public function testGenerateWithMissingNormalizer(): void
    {
        $object = (object)[];
        $this->getNormalizer()
            ->expects(self::once())
            ->method('normalize')
            ->with(self::identicalTo($object))
            ->willThrowException(new NotNormalizableValueException('foo'));

        $this->getLogger()
            ->expects(self::once())
            ->method('error')
            ->with(self::identicalTo(
                'Unable to normalize value for type "stdClass". foo. Ensure that a normalizer is registered '
                . 'with tag: "ibexa.rest.serializer.normalizer".'
            ));

        $this->getGenerator()->generateFieldTypeHash('fieldValue', $object);

        $this->assertSerializationSame(__FUNCTION__);
    }

    protected function getFieldTypeHashGenerator()
    {
        if (!isset($this->fieldTypeHashGenerator)) {
            $this->fieldTypeHashGenerator = $this->initializeFieldTypeHashGenerator();
        }

        return $this->fieldTypeHashGenerator;
    }

    protected function getGenerator()
    {
        if (!isset($this->generator)) {
            $this->generator = $this->initializeGenerator();
            $this->generator->startDocument('Version');
            $this->generator->startHashElement('Field');
        }

        return $this->generator;
    }

    private function getGeneratorOutput()
    {
        $this->getGenerator()->endHashElement('Field');

        return $this->getGenerator()->endDocument('Version');
    }

    private function assertSerializationSame(string $functionName): void
    {
        $fixtureFile = $this->getFixtureFile($functionName);
        $isXml = str_starts_with(basename($fixtureFile), 'Xml');

        $actualResult = $this->getGeneratorOutput();

        if ($isXml) {
            $expectedXml = new \DOMDocument();
            $expectedXml->preserveWhiteSpace = false;
            $expectedXml->formatOutput = true;
            $expectedXml->load($fixtureFile);

            $actualXml = new \DOMDocument();
            $actualXml->preserveWhiteSpace = false;
            $actualXml->formatOutput = true;
            $actualXml->loadXML($actualResult);

            self::assertSame($expectedXml->saveXML(), $actualXml->saveXML());
        } else {
            self::assertSame(
                file_get_contents($this->getFixtureFile($functionName)),
                $actualResult
            );
        }
    }

    private function getFixtureFile(string $functionName): string
    {
        return sprintf(
            '%s/_fixtures/%s__%s.out',
            __DIR__,
            $this->getRelativeClassIdentifier(),
            $functionName
        );
    }

    private function getRelativeClassIdentifier(): string
    {
        $fqClassName = static::class;

        return strtr(
            substr(
                $fqClassName,
                strlen(__NAMESPACE__) + 1
            ),
            ['\\' => '_']
        );
    }
}
