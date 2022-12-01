<?php

declare(strict_types=1);

namespace Oveleon\ContaoThemeManagerBridge\DependencyInjection;

use Oveleon\ContaoThemeManagerBridge\Licenser\ThemeManagerLicenser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoThemeManagerBridgeExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('product_installer', [
            'licenser' => [ThemeManagerLicenser::class],
        ]);
    }
}
