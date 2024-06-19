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


class OpenApiController extends Controller
{
    private $documentationFormats = [
        'jsonld' => ['application/ld+json'],
        'jsonopenapi' => ['application/vnd.openapi+json'],
        'html' => ['text/html'],
    ];

    public function __construct(
        ConfigResolverInterface $configResolver,
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly ProviderInterface $provider,
        private readonly ProcessorInterface $processor,
    ) {
    }

    private static ResourceNameCollection $resourceNameCollection;

    public function __invoke(Request $request)
    {
        static::$resourceNameCollection = $this->resourceNameCollectionFactory->create();
        $context = [
            'request' => $request,
            'spec_version' => (string) $request->query->get(LegacyOpenApiNormalizer::SPEC_VERSION),
        ];
        $request->attributes->set('_api_platform_disable_listeners', true);
        $operation = new Get(outputFormats: $this->documentationFormats, read: true, serialize: true, class: Entrypoint::class, provider: [self::class, 'provide']);
        $request->attributes->set('_api_operation', $operation);
        $body = $this->provider->provide($operation, [], $context);
        $operation = $request->attributes->get('_api_operation');

        return $this->processor->process($body, $operation, [], $context);
    }

    public static function provide(): Entrypoint
    {
        return new Entrypoint(static::$resourceNameCollection);
    }


    public function serveDocumentationAction(Request $request): JsonResponse
    {
        return $this->__invoke($request);
//        return new JsonResponse([
//            'status' => 'fdddiled',
//        ]);
    }

}
