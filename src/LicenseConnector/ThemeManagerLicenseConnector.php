<?php

namespace Oveleon\ContaoThemeManagerBridge\LicenseConnector;

use Contao\Controller;
use Oveleon\ContaoThemeManagerBridge\Controller\InstallProcessController;
use Oveleon\ContaoThemeManagerBridge\Controller\LicenseController;
use Oveleon\ContaoThemeManagerBridge\Controller\SystemCheckProcessController;
use Oveleon\ProductInstaller\LicenseConnector\AbstractLicenseConnector;
use Oveleon\ProductInstaller\LicenseConnector\Process\DefaultProcess;
use Oveleon\ProductInstaller\LicenseConnector\Step\ContaoManagerStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\LicenseStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\ProductStep;
use Oveleon\ProductInstaller\LicenseConnector\Step\ProcessStep;

/**
 * Configuration class for the licensor of Contao ThemeManager products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ThemeManagerLicenseConnector extends AbstractLicenseConnector
{
    function setSteps(): void
    {
        $router = Controller::getContainer()->get('router');
        $translator = Controller::getContainer()->get('translator');

        // Create processes
        $systemCheckProcess = (new DefaultProcess(
            $translator->trans('theme_manager_installer.processes.system_check.title', [], 'theme_manager_installer'),
            $translator->trans('theme_manager_installer.processes.system_check.description', [], 'theme_manager_installer')
        ))->addRoute(DefaultProcess::ROUTE_PROCESS, $router->generate(SystemCheckProcessController::class));

        $installProcess = (new DefaultProcess(
            $translator->trans('theme_manager_installer.processes.install.title', [], 'theme_manager_installer'),
            $translator->trans('theme_manager_installer.processes.install.description', [], 'theme_manager_installer')
        ))->addRoute(DefaultProcess::ROUTE_PROCESS, $router->generate(InstallProcessController::class));

        // Create steps
        $this->addSteps(
            // Add license step
            (new LicenseStep())
                ->addRoute(LicenseStep::ROUTE_CHECK_LICENSE, $router->generate(LicenseController::class)),

            // Add product preview step
            new ProductStep(),

            // Add contao manager authentication step
            new ContaoManagerStep(),

            // Add install process step
            (new ProcessStep())
                ->addProcesses(
                    $systemCheckProcess,
                    $installProcess
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
            'name'          => 'ThemeManager',
            'title'         => $translator->trans('theme_manager_installer.product.title', [], 'theme_manager_installer'),
            'description'   => $translator->trans('theme_manager_installer.product.description', [], 'theme_manager_installer'),
            'image'         => $translator->trans('theme_manager_installer.product.image', [], 'theme_manager_installer'),
        ];
    }
}
