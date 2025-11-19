<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Exception as ExceptionValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionTest extends ValueObjectVisitorBaseTest
{
    protected const NON_VERBOSE_ERROR_DESCRIPTION = 'An error has occurred. Please try again later or contact your Administrator.';

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private ?MockObject $translatorMock = null;

    public function testVisit(): string
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $result = $this->generateDocument($generator, $visitor);

        self::assertNotEmpty($result);

        return $result;
    }

    public function testVisitNonVerbose(): string
    {
        $this->getTranslatorMock()->method('trans')
             ->with('non_verbose_error', [], 'ibexa_repository_exceptions')
             ->willReturn(self::NON_VERBOSE_ERROR_DESCRIPTION);

        $visitor = $this->internalGetNonDebugVisitor();
        $visitor->setUriParser($this->getUriParser());
        $visitor->setRouter($this->getRouterMock());
        $visitor->setTemplateRouter($this->getTemplatedRouterMock());

        $generator = $this->getGenerator();

        $result = $this->generateDocument($generator, $visitor);

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsErrorCode(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'descendant' => [
                    'tag' => 'errorCode',
                    'content' => (string)$this->getExpectedStatusCode(),
                ],
            ],
            $result,
            'Invalid <ErrorMessage> element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsErrorMessage(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'descendant' => [
                    'tag' => 'errorMessage',
                    'content' => $this->getExpectedMessage(),
                ],
            ],
            $result,
            'Invalid <ErrorMessage> element.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsErrorDescription(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'descendant' => [
                    'tag' => 'errorDescription',
                ],
            ],
            $result,
            'Invalid <ErrorMessage> element.'
        );
    }

    /**
     * @depends testVisitNonVerbose
     */
    public function testNonVerboseErrorDescription(string $result): void
    {
        $document = new DOMDocument();
        $document->loadXML($result);
        $xpath = new DOMXPath($document);

        $nodeList = $xpath->query('//ErrorMessage/errorDescription');
        self::assertInstanceOf(DOMNodeList::class, $nodeList);

        $errorDescriptionNode = $nodeList->item(0);

        self::assertInstanceOf(DOMNode::class, $errorDescriptionNode);
        self::assertEquals(self::NON_VERBOSE_ERROR_DESCRIPTION, $errorDescriptionNode->textContent);
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsExceptionAttributes(string $result): void
    {
        $this->assertXMLTag(
            [
                'tag' => 'ErrorMessage',
                'attributes' => [
                    'media-type' => 'application/vnd.ibexa.api.ErrorMessage+xml',
                ],
            ],
            $result,
            'Invalid <ErrorMessage> attributes.'
        );
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsPreviousError(string $result): void
    {
        $dom = new DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath(
            $dom,
            '/ErrorMessage/Previous[@media-type="application/vnd.ibexa.api.ErrorMessage+xml"]'
        );
    }

    protected function getExpectedStatusCode(): int
    {
        return 500;
    }

    protected function getExpectedMessage(): string
    {
        return 'Internal Server Error';
    }

    protected function getException(): \Exception
    {
        return new \Exception('Test');
    }

    protected function internalGetVisitor(): ExceptionValueObjectVisitor
    {
        return new ExceptionValueObjectVisitor(true, $this->getTranslatorMock());
    }

    protected function internalGetNonDebugVisitor(): ExceptionValueObjectVisitor
    {
        return new ExceptionValueObjectVisitor(false, $this->getTranslatorMock());
    }

    protected function getTranslatorMock(): TranslatorInterface & MockObject
    {
        if (!isset($this->translatorMock)) {
            $this->translatorMock = $this->getMockBuilder(TranslatorInterface::class)
                 ->disableOriginalConstructor()
                 ->getMock();
        }

        return $this->translatorMock;
    }

    private function generateDocument(
        Xml $generator,
        ValueObjectVisitor $visitor
    ): string {
        $generator->startDocument(null);

        $previousException = new \Exception('Sub-test');
        $exception = new \Exception('Test', 0, $previousException);

        $this
            ->getVisitorMock()
            ->expects(self::once())
            ->method('visitValueObject')
            ->with($previousException);

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $exception
        );

        return $generator->endDocument(null);
    }
}
