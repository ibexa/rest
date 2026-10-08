<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Values;

use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Rest\Value as RestValue;

final class LanguageList extends RestValue
{
    /** @var Language[] */
    public array $languages;

    /**
     * @param array<Language> $languages
     */
    public function __construct(array $languages)
    {
        $this->languages = $languages;
    }
}
