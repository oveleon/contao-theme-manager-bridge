<?php

declare(strict_types=1);

namespace Oveleon\ContaoThemeManagerBridge\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Oveleon\ContaoThemeManagerBridge\ContaoThemeManagerBridge;
use Oveleon\ProductInstaller\ProductInstaller;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoThemeManagerBridge::class)
                        ->setLoadAfter([
                            ContaoCoreBundle::class,
                            ProductInstaller::class
                        ])
        ];
    }
}
