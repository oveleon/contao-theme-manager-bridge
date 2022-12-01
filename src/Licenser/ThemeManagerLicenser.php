<?php

namespace Oveleon\ContaoThemeManagerBridge\Licenser;

use Oveleon\ContaoThemeManagerBridge\Controller\LicenseController;
use Oveleon\ProductInstaller\Licenser\AbstractLicenser;
use Oveleon\ProductInstaller\Licenser\Step\DefaultProcess;
use Oveleon\ProductInstaller\Licenser\Step\LicenseStep;
use Oveleon\ProductInstaller\Licenser\Step\ProductStep;
use Oveleon\ProductInstaller\Licenser\Step\ProcessStep;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Configuration class for the licensor of Contao ThemeManager products.
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class ThemeManagerLicenser extends AbstractLicenser
{

    public function __construct(
        protected readonly RouterInterface $router,
        protected readonly TranslatorInterface $translator
    ){}

    function steps(): void
    {
        // Create processes
        $systemCheckProcess = new DefaultProcess(
            $this->translator->trans('theme_manager_installer.processes.title', [], 'theme_manager_installer'),
            $this->translator->trans('theme_manager_installer.processes.description', [], 'theme_manager_installer')
        );

        // Create steps
        $this->addSteps(
            (new LicenseStep())
                ->addRoutes($this->router->generate(LicenseController::class)),
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
    function config(): array
    {
        return [
            'icon'          => $this->translator->trans('theme_manager_installer.product.icon', [], 'theme_manager_installer'),
            'title'         => $this->translator->trans('theme_manager_installer.product.title', [], 'theme_manager_installer'),
            'description'   => $this->translator->trans('theme_manager_installer.product.description', [], 'theme_manager_installer')
        ];
    }
}
