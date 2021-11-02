<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Rest\Server\Values;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * Struct representing a resource OPTIONS response.
 */
class Options extends ValueObject
{
    /**
     * The methods allowed my the resource.
     *
     * @var array
     */
    public $allowedMethods;

    public function __construct($allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;
    }
}

class_alias(Options::class, 'EzSystems\EzPlatformRest\Server\Values\Options');
