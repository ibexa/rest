<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Rest\Server\Output\ValueObjectVisitor;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Ibexa\Core\MVC\Symfony\RequestStackAware;

/**
 * CachedValue value object visitor.
 */
class CachedValue extends ValueObjectVisitor
{
    use RequestStackAware;

    protected ConfigResolverInterface $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param \Ibexa\Rest\Server\Values\CachedValue $data
     */
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $visitor->visitValueObject($data->value);

        if ($this->getParameter('content.view_cache') !== true) {
            return;
        }

        $response = $visitor->getResponse();
        $response->setPublic();
        $response->setVary('Accept');

        if ($this->getParameter('content.ttl_cache') === true) {
            $response->setSharedMaxAge($this->getParameter('content.default_ttl'));
            $request = $this->getCurrentRequest();
            if (isset($request) && $request->headers->has('X-User-Hash')) {
                $response->setVary('X-User-Hash', false);
            }
        }

        if (isset($data->cacheTags['locationId'])) {
            $visitor->getResponse()->headers->set('X-Location-Id', $data->cacheTags['locationId']);
        }
    }

    public function getParameter(string $parameterName, mixed $defaultValue = null)
    {
        if ($this->configResolver->hasParameter($parameterName)) {
            return $this->configResolver->getParameter($parameterName);
        }

        return $defaultValue;
    }
}
