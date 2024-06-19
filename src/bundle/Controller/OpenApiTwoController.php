<?php

namespace Ibexa\Bundle\Rest\Controller;

use ApiPlatform\Metadata\Get;
use ApiPlatform\OpenApi\Serializer\LegacyOpenApiNormalizer;
use Ibexa\AdminUi\Form\DataMapper\SectionCreateMapper;
use Ibexa\AdminUi\Form\DataMapper\SectionUpdateMapper;
use Ibexa\AdminUi\Form\Factory\FormFactory;
use Ibexa\AdminUi\Form\SubmitHandler;
use Ibexa\AdminUi\UI\Service\PathService;
use Ibexa\Contracts\AdminUi\Notification\TranslatableNotificationHandlerInterface;
use Ibexa\Contracts\AdminUi\Permission\PermissionCheckerInterface;
use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Ibexa\Contracts\AdminUi\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;


class OpenApiTwoController extends Controller
{


    public function __construct(
    ) {
    }

    public function serveDocumentationAction(Request $request): JsonResponse
    {
//        return $this->__invoke($request);
        return new JsonResponse([
            'status' => 'fdddiled',
        ]);
    }

}
