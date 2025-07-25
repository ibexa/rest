<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Rest\Server\Service;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Rest\Values;
use Ibexa\Rest\Values\Root;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ExpressionRouterRootResourceBuilder.
 *
 * This class builds a Root from an array building the href's using ExpressionLanguage
 * to build href's from the router or templateRouter.
 *
 * Example $resourceConfig structure:
 *
 * array(
 *      'content' => array(
 *          'mediaType' => '',
 *          'href' => 'router.generate("ibexa.rest.list_content_types")',
 *      ),
 *      'usersByRoleId' => array(
 *          'mediaType' => 'UserRefList',
 *          'href' => 'templateRouter.generate("ibexa.rest.load_users", {roleId: "{roleId}"})',
 *      ),
 * )
 */
class ExpressionRouterRootResourceBuilder implements RootResourceBuilderInterface
{
    protected RouterInterface $router;

    protected RouterInterface $templateRouter;

    protected ConfigResolverInterface $configResolver;

    public function __construct(RouterInterface $router, RouterInterface $templateRouter, ConfigResolverInterface $configResolver)
    {
        $this->router = $router;
        $this->templateRouter = $templateRouter;
        $this->configResolver = $configResolver;
    }

    /**
     * Build root resource.
     */
    public function buildRootResource(): Root
    {
        $language = new ExpressionLanguage();

        $resources = [];
        foreach ($this->configResolver->getParameter('rest_root_resources') as $name => $resource) {
            $resources[] = new Values\Resource(
                $name,
                $resource['mediaType'],
                $language->evaluate($resource['href'], [
                    'router' => $this->router,
                    'templateRouter' => $this->templateRouter,
                ])
            );
        }

        return new Root($resources);
    }
}
