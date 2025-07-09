<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Exceptions\AbstractExceptionVisitor;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Exception value object visitor.
 */
class Exception extends AbstractExceptionVisitor
{
    /**
     * Is debug mode enabled?
     */
    protected bool $debug;

    protected ?TranslatorInterface $translator;

    /**
     * Construct from debug flag.
     */
    public function __construct(bool $debug = false, ?TranslatorInterface $translator = null)
    {
        $this->debug = $debug;
        $this->translator = $translator;
    }

    protected function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    protected function canDisplayExceptionMessage(): bool
    {
        return $this->debug;
    }

    protected function canDisplayExceptionTrace(): bool
    {
        return $this->debug;
    }

    protected function canDisplayPreviousException(): bool
    {
        return true;
    }
}
