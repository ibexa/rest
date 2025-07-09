<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Rest\Output\Exceptions;

use Exception;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\Base\Translatable;
use JMS\TranslationBundle\Annotation\Desc;
use JMS\TranslationBundle\Annotation\Ignore;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractExceptionVisitor extends ValueObjectVisitor
{
    /**
     * Mapping of HTTP status codes to their respective error messages.
     *
     * @var array<int, string>
     */
    protected static array $httpStatusCodes = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot",
        421 => 'There are too many connections from your internet address',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    ];

    /**
     * Returns HTTP status code.
     */
    protected function getStatus(): int
    {
        return 500;
    }

    /**
     * @param \Exception $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('ErrorMessage');

        $visitor->setHeader('Content-Type', $generator->getMediaType('ErrorMessage'));

        $statusCode = $this->generateErrorCode($generator, $visitor, $data);

        $errorMessage = $this->getErrorMessage($data, $statusCode);
        $generator->valueElement('errorMessage', $errorMessage);

        $errorDescription = $this->getErrorDescription($data, $statusCode);
        $generator->valueElement('errorDescription', $errorDescription);

        if ($this->canDisplayExceptionTrace()) {
            $generator->valueElement('trace', $data->getTraceAsString());
            $generator->valueElement('file', $data->getFile());
            $generator->valueElement('line', $data->getLine());
        }

        $previous = $data->getPrevious();
        if ($previous !== null && $this->canDisplayPreviousException()) {
            $generator->startObjectElement('Previous', 'ErrorMessage');
            $visitor->visitValueObject($previous);
            $generator->endObjectElement('Previous');
        }

        $generator->endObjectElement('ErrorMessage');
    }

    protected function generateErrorCode(Generator $generator, Visitor $visitor, Exception $e): int
    {
        $statusCode = $this->getStatus();
        $visitor->setStatus($statusCode);

        $generator->valueElement('errorCode', $statusCode);

        return $statusCode;
    }

    protected function getErrorMessage(Exception $data, int $statusCode): string
    {
        return static::$httpStatusCodes[$statusCode] ?? static::$httpStatusCodes[500];
    }

    protected function getErrorDescription(Exception $data, int $statusCode): string
    {
        $translator = $this->getTranslator();
        if ($statusCode < 500 || $this->canDisplayExceptionMessage()) {
            $errorDescription = $data instanceof Translatable && $translator
                ? /** @Ignore */
                $translator->trans($data->getMessageTemplate(), $data->getParameters(), 'ibexa_repository_exceptions')
                : $data->getMessage();
        } else {
            // Do not leak any file paths and sensitive data on production environments
            $errorDescription = $translator
                ? /** @Desc("An error has occurred. Please try again later or contact your Administrator.") */
                $translator->trans('non_verbose_error', [], 'ibexa_repository_exceptions')
                : 'An error has occurred. Please try again later or contact your Administrator.';
        }

        return $errorDescription;
    }

    protected function getTranslator(): ?TranslatorInterface
    {
        return null;
    }

    protected function canDisplayExceptionTrace(): bool
    {
        return false;
    }

    protected function canDisplayPreviousException(): bool
    {
        return false;
    }

    protected function canDisplayExceptionMessage(): bool
    {
        return false;
    }
}
