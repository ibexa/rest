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
     *
     * @var bool
     */
    protected $debug = false;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    protected $translator;

    /**
     * Construct from debug flag.
     *
     * @param bool $debug
     * @param \Symfony\Contracts\Translation\TranslatorInterface|null $translator
     */
    public function __construct($debug = false, ?TranslatorInterface $translator = null)
    {
        $this->debug = (bool)$debug;
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

class_alias(Exception::class, 'EzSystems\EzPlatformRest\Server\Output\ValueObjectVisitor\Exception');
