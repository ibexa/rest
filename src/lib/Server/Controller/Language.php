<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Controller;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Rest\Server\Controller as RestController;
use Ibexa\Rest\Server\Values\LanguageList;

final class Language extends RestController
{
    private LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function listLanguages(): LanguageList
    {
        $languages = $this->languageService->loadLanguages();

        if ($languages instanceof \Traversable) {
            $languages = iterator_to_array($languages);
        }

        return new LanguageList($languages);
    }

    public function viewLanguage(string $languageCode): \Ibexa\Contracts\Core\Repository\Values\Content\Language
    {
        return $this->languageService->loadLanguage($languageCode);
    }
}
