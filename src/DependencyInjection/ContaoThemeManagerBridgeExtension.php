<?php

declare(strict_types=1);

namespace Oveleon\ContaoThemeManagerBridge\DependencyInjection;

use Oveleon\ContaoThemeManagerBridge\LicenseConnector\ThemeManagerLicenseConnector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class ContaoThemeManagerBridgeExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {}

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('product_installer', [
            'license_connectors' => [ThemeManagerLicenseConnector::class],
        ]);
    }
}
