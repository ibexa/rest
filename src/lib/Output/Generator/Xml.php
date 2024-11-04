<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Rest\Output\Generator\Data\DataObjectInterface;
use Ibexa\Rest\Output\Normalizer\ArrayListNormalizer;
use Ibexa\Rest\Output\Normalizer\ArrayObjectNormalizer;
use Ibexa\Rest\Output\Normalizer\JsonObjectNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Xml extends Generator
{
    public const string OUTER_ELEMENT = 'outer_element';

    public function __construct(
        Xml\FieldTypeHashGenerator $fieldTypeHashGenerator,
        string $vendor = 'vnd.ibexa.api',
    ) {
        $this->fieldTypeHashGenerator = $fieldTypeHashGenerator;

        parent::__construct($vendor);
    }

    #[\Override]
    public function getMediaType(string $name): string
    {
        return $this->generateMediaTypeWithVendor($name, 'xml', $this->getVendor());
    }

    #[\Override]
    public function startList(string $name): void
    {
        $this->checkStartList($name);

        $this->isEmpty = false;

        $array = new Data\ArrayList($name, $this->json);

        $this->json->$name = $array;
        $this->json = $array;
    }

    #[\Override]
    public function startAttribute(string $name, mixed $value): void
    {
        $this->checkStartAttribute($name);

        $this->json->{'@' . $name} = $value;
    }

    #[\Override]
    public function serializeBool(mixed $boolValue): string
    {
        return $boolValue ? 'true' : 'false';
    }

    #[\Override]
    public function startValueElement(string $name, mixed $value, array $attributes = []): void
    {
        $this->checkStartValueElement($name);

        if (empty($attributes)) {
            $jsonValue = $value;
        } else {
            $jsonValue = new Json\JsonObject($this->json);
            foreach ($attributes as $attributeName => $attributeValue) {
                $jsonValue->{'@' . $attributeName} = $attributeValue;
            }

            $jsonValue->{'#'} = $value;
        }

        if ($this->json instanceof Json\ArrayObject || $this->json instanceof Data\ArrayList) {
            $this->json->append($jsonValue);
        } else {
            $this->json->$name = $jsonValue;
        }
    }

    #[\Override]
    public function endDocument(mixed $data): string
    {
        $this->checkEndDocument($data);

        $data = $this->getData();

        if (!$data instanceof Json\JsonObject) {
            throw new \LogicException('Expected an instance of JsonObject');
        }

        $vars = get_object_vars($data);
        $encoderContext = $this->getEncoderContext($vars);

        $normalizers = [
            new ArrayListNormalizer(),
            new JsonObjectNormalizer(),
            new ArrayObjectNormalizer(),
            new ObjectNormalizer(),
        ];
        $encoders = [new XmlEncoder()];
        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($data, 'xml', $encoderContext);
    }

    #[\Override]
    public function getData(): DataObjectInterface
    {
        return $this->json;
    }

    #[\Override]
    public function getEncoderContext(array $data): array
    {
        return [
            XmlEncoder::ROOT_NODE_NAME => array_key_first($data),
            XmlEncoder::VERSION => '1.0',
            XmlEncoder::ENCODING => 'UTF-8',
            XmlEncoder::AS_COLLECTION => true,
            XmlEncoder::FORMAT_OUTPUT => true,
            self::OUTER_ELEMENT => true,
        ];
    }
}
