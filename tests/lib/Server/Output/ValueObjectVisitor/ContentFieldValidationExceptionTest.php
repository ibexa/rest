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
    public function testVisit(): string
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

        self::assertNotEmpty($result);

        return $result;
    }

    /**
     * @depends testVisit
     */
    public function testResultContainsErrorDescription(string $result): void
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
     * @depends testVisit
     */
    public function testResultContainsErrorDetails(string $result): void
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

    protected function getExpectedStatusCode(): int
    {
        return 400;
    }

    protected function getExpectedMessage(): string
    {
        return 'Bad Request';
    }

    protected function getExpectedDescription(): string
    {
        return 'Content fields did not validate';
    }

    protected function getException(): ContentFieldValidationException
    {
        return new ContentFieldValidationException(
            new CoreContentFieldValidationException([
                1 => [
                    'eng-GB' => [
                        new ValidationError(
                            "Value for required field definition '%identifier%' with language '%languageCode%' is empty",
                            null,
                            ['%identifier%' => 'name', '%languageCode%' => 'eng-GB'],
                            'empty'
                        ),
                    ],
                ],
                2 => [
                    'eng-GB' => [
                        new ValidationError(
                            'The value must be a valid email address.',
                            null,
                            [],
                            'email'
                        ),
                    ],
                ],
            ])
        );
    }

    protected function internalGetVisitor(): ValueObjectVisitor\ContentFieldValidationException
    {
        return new ValueObjectVisitor\ContentFieldValidationException(false, new Translator('eng-GB'));
    }
}
