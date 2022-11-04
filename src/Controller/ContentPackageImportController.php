<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Exception;
use Oveleon\ContaoThemeManagerBridge\Controller\BackendModule\AssistantModuleController;
use Oveleon\ContaoThemeManagerBridge\Import\ContentPackageImport;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/contao/content/import",
 *     name=ContentPackageImportController::class,
 *     defaults={"_scope": "backend"}
 * )
 */
class ContentPackageImportController
{
    public function __construct(
        private readonly ContentPackageImport $importer,
        private readonly RequestStack $requestStack,
        private readonly RouterInterface $router
    ){}

    /**
     * @throws Exception
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        // Set the root page if one is selected
        if($rootPage = $request->get('page'))
        {
            $this->importer->setRootPage((int) $rootPage);
        }

        // Start import
        $this->importer->import($request->get('filepath'));

        throw new RedirectResponseException($this->router->generate(AssistantModuleController::class));
    }
}
