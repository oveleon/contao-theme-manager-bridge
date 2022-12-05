<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/product/theme-manager/license',
    name:       LicenseController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class LicenseController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator
    ){}

    /**
     * Check license
     */
    public function __invoke(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest()->toArray();

        if(!$license = $request['license'])
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => $this->translator->trans('theme_manager_installer.license.errors.license_empty', [], 'theme_manager_installer')
                ]
            ]);
        }

        // ToDo: Check license by product-licenser (server)
        // Fixme: Simulate product-licenser call
        if($license === 'ABC')
        {
            return new JsonResponse([
                'products' => [
                    [
                        'name' => 'Vorlagen-Paket MEDIUM',
                        'version' => '1.0.0',
                        'image' => 'https://avatars.githubusercontent.com/u/44843847?s=200&v=4',
                        'description' => 'Um ein triviales Beispiel zu nehmen, wer von uns unterzieht sich je anstrengender körperlicher Betätigung, außer um Vorteile daraus zu ziehen?',
                        'registrable' => true,
                        'repository' => [
                            'company' => 'oveleon',
                            'repository' => 'content-package-1'
                        ]
                    ]
                ],
                'key' => 'license-connector-key-to-get-github-auth-key'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'fields' => [
                'license' => $this->translator->trans('theme_manager_installer.license.errors.license_not_found', [], 'theme_manager_installer')
            ]
        ]);
    }
}
