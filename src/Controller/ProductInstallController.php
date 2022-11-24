<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '%contao.backend.route_prefix%/installer',
    defaults: ['_scope' => 'backend', '_token_check' => false],
    methods: ['POST']
)]
class ProductInstallController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ){}

    #[Route('/check', name: 'installer_check', methods: ['POST'])]
    public function checkLicense(): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest()->toArray();

        if(!$license = $request['license'])
        {
            return new JsonResponse([
                'error'  => true,
                'fields' => [
                    'license' => 'Bitte geben Sie eine gültige Lizenz an.'
                ]
            ]);
        }

        // ToDo: Check license by server
        // Fixme: Simulate validation
        if($license === 'ABC')
        {
            return new JsonResponse([
                'name' => 'Vorlagen-Paket MEDIUM',
                'version' => '1.0.0',
                'image' => 'https://avatars.githubusercontent.com/u/44843847?s=200&v=4',
                'description' => 'Um ein triviales Beispiel zu nehmen, wer von uns unterzieht sich je anstrengender körperlicher Betätigung, außer um Vorteile daraus zu ziehen?',
                'company' => 'oveleon',
                'repository' => 'content-package-1',
                'key' => 'license-connector-key-to-get-github-auth-key'
            ]);
        }

        return new JsonResponse([
            'error' => true,
            'fields' => [
                'license' => 'Es konnte kein Produkt unter der angegebenen Lizenz gefunden werden. Bitte überprüfen Sie Ihre Eingabe und beachten Sie Groß- und Kleinschreibung.'
            ]
        ]);
    }

    #[Route('/install', name: 'installer_install', methods: ['POST'])]
    public function install(): JsonResponse
    {
        // ToDo: Install based on key to get the github auth

    }
}
