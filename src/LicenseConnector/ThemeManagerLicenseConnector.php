<?php

namespace Oveleon\ContaoThemeManagerBridge\LicenseConnector;

use Contao\Controller;
use Oveleon\ContaoThemeManagerBridge\Controller\API\InstallProcessController;
use Oveleon\ContaoThemeManagerBridge\Controller\API\SystemCheckProcessController;
use Oveleon\ProductInstaller\LicenseConnector\AbstractLicenseConnector;
use Oveleon\ProductInstaller\LicenseConnector\Process\ApiProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\ContaoManagerProcess;
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
        $systemCheckProcess = (new ApiProcess(
            $translator->trans('theme_manager_installer.processes.system_check.title', [], 'theme_manager_installer'),
            $translator->trans('theme_manager_installer.processes.system_check.description', [], 'theme_manager_installer')
        ))->addRoute(ApiProcess::ROUTE, $router->generate(SystemCheckProcessController::class));

        $installProcess = (new ApiProcess(
            $translator->trans('theme_manager_installer.processes.install.title', [], 'theme_manager_installer'),
            $translator->trans('theme_manager_installer.processes.install.description', [], 'theme_manager_installer')
        ))->addRoute(ApiProcess::ROUTE, $router->generate(InstallProcessController::class));

        // Create steps
        $this->addSteps(
            // Add license step
            new LicenseStep(),

            // Add product preview step
            new ProductStep(),

            // Add contao manager step
            new ContaoManagerStep(),

            // Add install process step
            (new ProcessStep())
                ->addProcesses(
                    new ContaoManagerProcess(),
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
            'entry'         => 'http://contao-shop.local/api'
        ];
    }
}
