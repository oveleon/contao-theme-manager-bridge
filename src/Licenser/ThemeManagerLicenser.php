<?php

namespace Oveleon\ContaoThemeManagerBridge\Licenser;

use Contao\Controller;
use Oveleon\ContaoThemeManagerBridge\Controller\LicenseController;
use Oveleon\ProductInstaller\Licenser\AbstractLicenser;
use Oveleon\ProductInstaller\Licenser\Process\DefaultProcess;
use Oveleon\ProductInstaller\Licenser\Step\LicenseStep;
use Oveleon\ProductInstaller\Licenser\Step\ProductStep;
use Oveleon\ProductInstaller\Licenser\Step\ProcessStep;

/**
 * Configuration class for the licensor of Contao ThemeManager products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ThemeManagerLicenser extends AbstractLicenser
{
    function setSteps(): void
    {
        $router = Controller::getContainer()->get('router');
        $translator = Controller::getContainer()->get('translator');

        // Create processes
        $systemCheckProcess = new DefaultProcess(
            $translator->trans('theme_manager_installer.processes.title', [], 'theme_manager_installer'),
            $translator->trans('theme_manager_installer.processes.description', [], 'theme_manager_installer')
        );

        // Create steps
        $this->addSteps(
            (new LicenseStep())
                ->addRoutes($router->generate(LicenseController::class)),
            new ProductStep(),
            (new ProcessStep())
                ->addProcesses(
                    $systemCheckProcess
                )
        );
    }

    /**
     * @inheritDoc
     */
    function getConfig(): array
    {
        $translator = Controller::getContainer()->get('translator');

        return [
            'image'         => $translator->trans('theme_manager_installer.product.image', [], 'theme_manager_installer'),
            'title'         => $translator->trans('theme_manager_installer.product.title', [], 'theme_manager_installer'),
            'description'   => $translator->trans('theme_manager_installer.product.description', [], 'theme_manager_installer')
        ];
    }
}
