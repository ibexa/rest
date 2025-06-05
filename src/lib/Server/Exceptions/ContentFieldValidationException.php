<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Exceptions;

use Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException as APIContentFieldValidationException;

/**
 * Exception thrown if one or more content fields did not validate.
 */
class ContentFieldValidationException extends BadRequestException
{
    /**
     * Contains an array of field ValidationError objects indexed with FieldDefinition id and language code.
     *
     * @see \Ibexa\Core\Base\Exceptions\ContentFieldValidationException
     *
     * @var array<int, array<string, \Ibexa\Contracts\Core\FieldType\ValidationError[]>>
     */
    protected array $errors;

    public function __construct(APIContentFieldValidationException $e)
    {
        $this->errors = $e->getFieldErrors();

        parent::__construct($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * Returns an array of field validation error messages.
     *
     * @return array<int, array<string, \Ibexa\Contracts\Core\FieldType\ValidationError[]>>
     */
    public function getFieldErrors(): array
    {
        return $this->errors;
    }
}
