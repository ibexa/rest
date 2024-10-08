<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use DOMDocument;
use DOMXPath;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Rest\Output\Generator\Xml;
use Ibexa\Rest\Server\Output\ValueObjectVisitor\Exception as ExceptionValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionTest extends ValueObjectVisitorBaseTest
{
    protected const NON_VERBOSE_ERROR_DESCRIPTION = 'An error has occurred. Please try again later or contact your Administrator.';

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translatorMock;

    /**
     * Test the Exception visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $result = $this->generateDocument($generator, $visitor);

        self::assertNotNull($result);

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

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ErrorMessage element and error code.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorCode($result)
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
     * Test if result contains ErrorMessage element.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorMessage($result)
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
     * Test if result contains ErrorMessage element and description.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorDescription($result)
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
        $errorDescriptionNode = $nodeList->item(0);

        self::assertEquals(self::NON_VERBOSE_ERROR_DESCRIPTION, $errorDescriptionNode->textContent);
    }

    /**
     * Test if ErrorMessage element contains required attributes.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsExceptionAttributes($result)
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
     * Test if result contains ErrorMessage element.
     *
     * @depends testVisit
     */
    public function testResultContainsPreviousError($result)
    {
        $dom = new \DOMDocument();
        $dom->loadXml($result);

        $this->assertXPath(
            $dom,
            '/ErrorMessage/Previous[@media-type="application/vnd.ibexa.api.ErrorMessage+xml"]'
        );
    }

    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode()
    {
        return 500;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage()
    {
        return 'Internal Server Error';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException()
    {
        return new \Exception('Test');
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Exception
     */
    protected function internalGetVisitor()
    {
        return new ExceptionValueObjectVisitor(true, $this->getTranslatorMock());
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\Exception
     */
    protected function internalGetNonDebugVisitor(): ExceptionValueObjectVisitor
    {
        return new ExceptionValueObjectVisitor(false, $this->getTranslatorMock());
    }

    protected function getTranslatorMock(): TranslatorInterface
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
