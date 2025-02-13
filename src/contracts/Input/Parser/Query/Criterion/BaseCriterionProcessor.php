<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Rest\Input\Parser\Query\Criterion;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;

/**
 * @template TCriterion
 *
 * @internal
 */
abstract class BaseCriterionProcessor implements CriterionProcessorInterface
{
    private const CRITERION_SUFFIX = 'Criterion';
    private const LOGICAL_OPERATOR_CRITERION_MAP = [
        'AND' => 'LogicalAnd',
        'OR' => 'LogicalOr',
        'NOT' => 'LogicalNot',
    ];

    private ParsingDispatcher $parsingDispatcher;

    public function __construct(ParsingDispatcher $parsingDispatcher)
    {
        $this->parsingDispatcher = $parsingDispatcher;
    }

    final public function processCriteria(array $criteriaData): iterable
    {
        if (empty($criteriaData)) {
            yield from [];
        }

        foreach ($criteriaData as $criterionName => $criterionData) {
            $mediaType = $this->getCriterionMediaType($criterionName);

            try {
                yield $this->parsingDispatcher->parse([$criterionName => $criterionData], $mediaType);
            } catch (Exceptions\Parser $e) {
                throw new Exceptions\Parser($this->getParserInvalidCriterionMessage($criterionName), 0, $e);
            }
        }
    }

    private function getCriterionMediaType(string $criterionName): string
    {
        if (self::CRITERION_SUFFIX === substr($criterionName, -strlen(self::CRITERION_SUFFIX))) {
            $criterionName = substr($criterionName, 0, -strlen(self::CRITERION_SUFFIX));
        }

        if (isset(self::LOGICAL_OPERATOR_CRITERION_MAP[$criterionName])) {
            $criterionName = self::LOGICAL_OPERATOR_CRITERION_MAP[$criterionName];
        }

        $mediaTypePrefix = $this->getMediaTypePrefix();
        if ('.' !== substr($mediaTypePrefix, strlen($mediaTypePrefix) - 1)) {
            $mediaTypePrefix .= '.';
        }

        return  $mediaTypePrefix . $criterionName;
    }

    abstract protected function getMediaTypePrefix(): string;

    abstract protected function getParserInvalidCriterionMessage(string $criterionName): string;
}
