<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('%contao.backend.route_prefix%/product/theme-manager/install',
    name:       InstallProcessController::class,
    defaults:   ['_scope' => 'backend', '_token_check' => false],
    methods:    ['POST']
)]
class InstallProcessController
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
        // ToDo: Install based on key to get the github auth

        return new JsonResponse([
            'error' => true,
            'messages' => [
                'Coming soon...'
            ]
        ], Response::HTTP_NOT_ACCEPTABLE);
    }
}
