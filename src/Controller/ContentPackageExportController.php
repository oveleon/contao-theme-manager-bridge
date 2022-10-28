<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Contao\StringUtil;
use Exception;
use Oveleon\ContaoThemeManagerBridge\Export\ContentPackageExport;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao/content/export",
 *     name=ContentPackageExportController::class,
 *     defaults={"_scope": "backend"}
 * )
 */
class ContentPackageExportController
{
    public function __construct(
        private readonly ContentPackageExport $exporter,
        private readonly RequestStack $requestStack,
    ){}

    /**
     * @throws Exception
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $this->exporter
            ->setManifestData([
                'name'    => $request->get('name'),
                'version' => $request->get('version')
            ])
            ->addDirectory($request->get('directory'))
            ->setFileName(StringUtil::sanitizeFileName($request->get('name')))
            ->export($request->get('theme'), $request->get('page'))
            ->sendToBrowser();

        return new Response('');
    }
}
