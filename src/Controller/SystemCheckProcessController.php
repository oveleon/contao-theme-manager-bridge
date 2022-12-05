<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/product/theme-manager/systemcheck',
    name:       SystemCheckProcessController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class SystemCheckProcessController
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        if(InstalledVersions::isInstalled('contao-thememanager/core'))
        {
            return new JsonResponse([
                'status' => 'OK'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'messages' => [
                $this->translator->trans('theme_manager_installer.processes.system_check.error.core_not_found', [], 'theme_manager_installer')
            ]
        ], Response::HTTP_NOT_ACCEPTABLE);
    }
}
