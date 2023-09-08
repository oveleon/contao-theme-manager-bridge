<?php

namespace Oveleon\ContaoThemeManagerBridge\LicenseConnector;

use Contao\Controller;
use Oveleon\ProductInstaller\LicenseConnector\AbstractLicenseConnector;
use Oveleon\ProductInstaller\LicenseConnector\Process\ContaoManagerProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\DownloadProcess;
use Oveleon\ProductInstaller\LicenseConnector\Process\RegisterProductProcess;
use Oveleon\ProductInstaller\LicenseConnector\Step\AdvertisingStep;
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
        // Create steps
        $this->addSteps(
            // Add license step
            new LicenseStep(),

            // Add product preview step
            new ProductStep(),

            // Add advertising step
            new AdvertisingStep(),

            // Add contao manager step
            new ContaoManagerStep(),

            // Add install process step
            (new ProcessStep())
                ->addProcesses(
                    new DownloadProcess(),
                    new ContaoManagerProcess(),
                    new RegisterProductProcess()
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
