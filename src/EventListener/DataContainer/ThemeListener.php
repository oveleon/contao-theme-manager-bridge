<?php

namespace Oveleon\ContaoThemeManagerBridge\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\StringUtil;
use Oveleon\ContaoThemeManagerBridge\Controller\BackendModule\AssistantModuleController;
use Symfony\Component\Routing\RouterInterface;

class ThemeListener
{
    public function __construct(
        private readonly RouterInterface $router
    ){}

    /**
     * @Callback(table="tl_theme", target="list.global_operations.theme_assistant.button")
     */
    public function addGlobalOperationAssistentButton(string $href, string $label, string $title, string $class, string $attributes): string
    {
        return vsprintf('<a href="%s" class="%s" title="%s" %s>%s</a> ', [
            $this->router->generate(AssistantModuleController::class),
            $class,
            StringUtil::specialchars($title),
            $attributes,
            $label
        ]);
    }
}
