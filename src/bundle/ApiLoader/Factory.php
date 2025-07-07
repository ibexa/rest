<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Rest\ApiLoader;

use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\RequestStackAware;
use Ibexa\Rest\FieldTypeProcessor\BinaryProcessor;
use Ibexa\Rest\FieldTypeProcessor\ImageAssetFieldTypeProcessor;
use Ibexa\Rest\FieldTypeProcessor\ImageProcessor;
use Ibexa\Rest\FieldTypeProcessor\MediaProcessor;
use Symfony\Component\Routing\RouterInterface;

class Factory
{
    use RequestStackAware;

    protected ConfigResolverInterface $configResolver;

    protected Repository $repository;

    public function __construct(ConfigResolverInterface $configResolver, Repository $repository)
    {
        $this->configResolver = $configResolver;
        $this->repository = $repository;
    }

    public function getBinaryFileFieldTypeProcessor(): BinaryProcessor
    {
        $request = $this->getCurrentRequest();
        $hostPrefix = isset($request) ? rtrim($request->getUriForPath('/'), '/') : '';

        return new BinaryProcessor(sys_get_temp_dir(), $hostPrefix);
    }

    public function getMediaFieldTypeProcessor(): MediaProcessor
    {
        return new MediaProcessor(sys_get_temp_dir());
    }

    /**
     * Factory for ezpublish_rest.field_type_processor.ibexa_image.
     */
    public function getImageFieldTypeProcessor(RouterInterface $router): ImageProcessor
    {
        $variationsIdentifiers = array_keys($this->configResolver->getParameter('image_variations'));
        sort($variationsIdentifiers);

        return new ImageProcessor(
            // Config for local temp dir
            // @todo get configuration
            sys_get_temp_dir(),
            // URL schema for image links
            // @todo get configuration
            $router,
            // Image variations (names only)
            $variationsIdentifiers
        );
    }

    public function getImageAssetFieldTypeProcessor(
        RouterInterface $router
    ): ImageAssetFieldTypeProcessor {
        $variationsIdentifiers = array_keys($this->configResolver->getParameter('image_variations'));
        sort($variationsIdentifiers);

        return new ImageAssetFieldTypeProcessor(
            $router,
            $this->repository->getContentService(),
            $this->configResolver->getParameter('fieldtypes.ibexa_image_asset.mappings'),
            $variationsIdentifiers
        );
    }
}
