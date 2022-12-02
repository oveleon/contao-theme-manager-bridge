<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Composer\InstalledVersions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(
    '%contao.backend.route_prefix%/product/theme-manager/systemcheck',
    defaults: ['_scope' => 'backend', '_token_check' => false],
    methods: ['POST']
)]
class SystemCheckProcessController
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

        if(InstalledVersions::isInstalled('contao-thememanager/core'))
        {
            return new JsonResponse([
                'status' => 'OK'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'messages' => [
                'Contao ThemeManager Core muss installiert sein.'
            ]
        ], Response::HTTP_NOT_ACCEPTABLE);
    }
}
