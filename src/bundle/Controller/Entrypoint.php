<?php

namespace Ibexa\Bundle\Rest\Controller;

use ApiPlatform\Metadata\Resource\ResourceNameCollection;

/**
* The first path you will see in the API.
*
* @author Amrouche Hamza <hamza.simperfit@gmail.com>
*/
final class Entrypoint
{
    public function __construct(private readonly ResourceNameCollection $resourceNameCollection)
    {
    }

    public function getResourceNameCollection(): ResourceNameCollection
    {
        return $this->resourceNameCollection;
    }
}
