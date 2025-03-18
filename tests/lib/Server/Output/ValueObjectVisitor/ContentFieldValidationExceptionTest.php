<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Tests\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Core\Base\Exceptions\ContentFieldValidationException as CoreContentFieldValidationException;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Rest\Server\Exceptions\ContentFieldValidationException;
use Ibexa\Rest\Server\Output\ValueObjectVisitor;
use Ibexa\Tests\Rest\Output\ValueObjectVisitorBaseTest;
use Symfony\Component\Translation\Translator;

class ContentFieldValidationExceptionTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the ContentFieldValidationException visitor.
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor = $this->getVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument(null);

        $exception = $this->getException();

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $exception
        );

        $result = $generator->endDocument(null);

        self::assertNotNull($result);

        return $result;
    }

    /**
     * Test if result contains ErrorMessage element and description.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorDescription($result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'errorDescription',
                'content' => $this->getExpectedDescription(),
            ],
            $result,
            'Missing <errorDescription> element.'
        );
    }

    /**
     * Test if result contains ErrorMessage element and details.
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsErrorDetails($result): void
    {
        self::assertXMLTag(
            [
                'tag' => 'errorDetails',
            ],
            $result,
            'Missing <errorDetails> element.'
        );

        self::assertXMLTag(
            [
                'tag' => 'field',
            ],
            $result,
            'Missing <field> element.'
        );
    }

    /**
     * Get expected status code.
     *
     * @return int
     */
    protected function getExpectedStatusCode(): int
    {
        return 400;
    }

    /**
     * Get expected message.
     *
     * @return string
     */
    protected function getExpectedMessage(): string
    {
        return 'Bad Request';
    }

    /**
     * Get expected description.
     *
     * @return string
     */
    protected function getExpectedDescription(): string
    {
        return 'Content fields did not validate';
    }

    /**
     * Gets the exception.
     *
     * @return \Exception
     */
    protected function getException(): ContentFieldValidationException
    {
        return new ContentFieldValidationException(
            new CoreContentFieldValidationException([
                1 => [
                    'eng-GB' => new ValidationError(
                        "Value for required field definition '%identifier%' with language '%languageCode%' is empty",
                        null,
                        ['%identifier%' => 'name', '%languageCode%' => 'eng-GB'],
                        'empty'
                    ),
                ],
                2 => [
                    'eng-GB' => new ValidationError(
                        'The value must be a valid email address.',
                        null,
                        [],
                        'email'
                    ),
                ],
            ])
        );
    }

    /**
     * Gets the exception visitor.
     *
     * @return \Ibexa\Rest\Server\Output\ValueObjectVisitor\ContentFieldValidationException
     */
    protected function internalGetVisitor(): ValueObjectVisitor\ContentFieldValidationException
    {
        return new ValueObjectVisitor\ContentFieldValidationException(false, new Translator('eng-GB'));
    }
}
