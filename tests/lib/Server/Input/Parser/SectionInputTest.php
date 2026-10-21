<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Input\Parser;

use Ibexa\Contracts\Core\Repository\Values\Content\SectionCreateStruct;
use Ibexa\Contracts\Rest\Exceptions\Parser;
use Ibexa\Core\Repository\SectionService;
use Ibexa\Rest\Server\Input\Parser\SectionInput;
use PHPUnit\Framework\MockObject\Stub;

class SectionInputTest extends BaseTestCase
{
    public function testParse(): void
    {
        $inputArray = [
            'name' => 'Name Foo',
            'identifier' => 'Identifier Bar',
        ];

        $sectionInput = $this->getParser();
        $result = $sectionInput->parse($inputArray, $this->getParsingDispatcherStub());

        self::assertEquals(
            new SectionCreateStruct($inputArray),
            $result,
            'SectionCreateStruct not created correctly.'
        );
    }

    public function testParseExceptionOnMissingIdentifier(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'identifier\' attribute for SectionInput.');
        $inputArray = [
            'name' => 'Name Foo',
        ];

        $sectionInput = $this->getParser();
        $sectionInput->parse($inputArray, $this->getParsingDispatcherStub());
    }

    public function testParseExceptionOnMissingName(): void
    {
        $this->expectException(Parser::class);
        $this->expectExceptionMessage('Missing \'name\' attribute for SectionInput.');
        $inputArray = [
            'identifier' => 'Identifier Bar',
        ];

        $sectionInput = $this->getParser();
        $sectionInput->parse($inputArray, $this->getParsingDispatcherStub());
    }

    protected function internalGetParser(): SectionInput
    {
        return new SectionInput(
            $this->getSectionServiceMock()
        );
    }

    protected function getSectionServiceMock(): SectionService & Stub
    {
        $sectionServiceMock = self::createStub(SectionService::class);

        $sectionServiceMock
            ->method('newSectionCreateStruct')
            ->willReturn(
                new SectionCreateStruct()
            );

        return $sectionServiceMock;
    }
}
