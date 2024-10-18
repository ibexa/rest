<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Output\Generator\InMemory;

use Ibexa\Rest\Output\Generator\Data;
use Ibexa\Rest\Output\Generator\Data\ArrayList;
use Ibexa\Rest\Output\Generator\Json;
use Ibexa\Rest\Output\Normalizer\ArrayListNormalizer;
use Ibexa\Rest\Output\Normalizer\ArrayObjectNormalizer;
use Ibexa\Rest\Output\Normalizer\JsonObjectNormalizer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class Xml extends Json
{
    public const string OUTER_ELEMENT = 'outer_element';

    public function getMediaType($name): string
    {
        return $this->generateMediaTypeWithVendor($name, 'xml', $this->vendor);
    }

    #[\Override]
    public function startList($name): void
    {
        $this->checkStartList($name);
        $array = new Data\ArrayList($name, $this->json);

        $this->json->$name = $array;
        $this->json = $array;
    }

    public function startAttribute($name, $value): void
    {
        $this->checkStartAttribute($name);

        $this->json->{'@' . $name} = $value;
    }

    public function serializeBool($boolValue): string
    {
        return $boolValue ? 'true' : 'false';
    }

    public function startValueElement(string $name, $value, array $attributes = []): void
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

        if ($this->json instanceof Json\ArrayObject || $this->json instanceof ArrayList) {
            $this->json->append($jsonValue);
        } else {
            $this->json->$name = $jsonValue;
        }
    }

    public function endDocument(mixed $data): string
    {
        parent::endDocument($data);

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
