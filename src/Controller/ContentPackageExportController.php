<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Oveleon\ContaoThemeManagerBridge\Export\ContentPackageExport;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contao/theme/export/{id}",
 *     name=ContentPackageExportController::class,
 *     defaults={"_scope": "backend"}
 * )
 */
class ContentPackageExportController
{
    public function __construct(
        private readonly ContentPackageExport $exporter
    ){}

    public function __invoke(int $id): void
    {
        $this->exporter->export($id);
    }
}
