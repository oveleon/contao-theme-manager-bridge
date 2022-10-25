<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller\BackendModule;

use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\Message;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\ThemeModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

/**
 * @Route("/contao/theme/assistant",
 *     name=AssistantModuleController::class,
 *     defaults={"_scope": "backend"}
 * )
 */
class AssistantModuleController extends AbstractController
{
    private array $sections = [];

    public function __construct(
        private readonly TwigEnvironment $twig,
        private readonly TranslatorInterface $translator,
        private readonly Studio $studio
    ){}

    public function __invoke(): Response
    {
        Controller::loadLanguageFile('theme_assistant');

        $this->loadThemeSection();
        $this->loadContentPackageSection();

        return new Response($this->twig->render(
            '@ContaoThemeManagerBridge/assistant.html.twig',
            [
                'label' => [
                    'headline'  => $this->translator->trans('theme_assistant.headline', [], 'contao_default'),
                    'backLabel' => $this->translator->trans('MSC.backBT', [], 'contao_default'),
                    'backTitle' => $this->translator->trans('MSC.backBTTitle', [], 'contao_default')
                ],
                'action' => [
                    'back' => 'contao?do=themes',
                ],
                'messages' => Message::generate(),
                'sections' => $this->sections
            ]
        ));
    }

    /**
     * Load theme section
     */
    private function loadThemeSection(): void
    {
        if($manifest = $this->readThemeManifest())
        {
            // Get theme logo
            $figure = $this->studio
                ->createFigureBuilder()
                ->setSize([0, 60])
                ->fromPath($manifest['path'] . '/' . $manifest['logo'])
                ->buildIfResourceExists();

            if($figure)
            {
                $template = new FrontendTemplate('image');
                $figure->applyLegacyTemplateData($template);

                $manifest['image'] = $template->parse();
            }
        }

        $this->sections[] = [
            'title'  => $this->translator->trans('theme_assistant.theme.title', [], 'contao_default'),
            'module' => $this->twig->render(
                '@ContaoThemeManagerBridge/theme.html.twig',
                [
                    'manifest' => $manifest,
                    'label'    => [
                        'version' => $this->translator->trans('theme_assistant.theme.label.version', [], 'contao_default'),
                        'docs'    => $this->translator->trans('theme_assistant.label.docs', [], 'contao_default'),
                        'info'    => $this->translator->trans('theme_assistant.label.info', [], 'contao_default'),
                        'store'   => $this->translator->trans('theme_assistant.label.store', [], 'contao_default'),
                        'empty'   => $this->translator->trans('theme_assistant.theme.label.empty', [], 'contao_default')
                    ],
                    'link'     => [
                        'store'   => $this->translator->trans('theme_assistant.link.store', [], 'contao_default'),
                        'docs'    => $this->translator->trans('theme_assistant.link.docs', [], 'contao_default'),
                        'info'    => $this->translator->trans('theme_assistant.link.info', [], 'contao_default')
                    ]
                ]
            )
        ];
    }

    /**
     * Load content package section
     */
    private function loadContentPackageSection(): void
    {
        $packages = null;

        if($contentPackages = $this->getContentPackages())
        {
            foreach ($contentPackages as $contentPackage)
            {
                $isInstalled = ThemeModel::countBy(['contentPackage=?'], [$contentPackage->getFilename()]);

                $tag = match($isInstalled) {
                    0       => $this->translator->trans('theme_assistant.label.installable', [], 'contao_default'),
                    default => $this->translator->trans('theme_assistant.label.installed', [], 'contao_default')
                };

                $packages[] = [
                    'path'        => $contentPackage->getRealPath(),
                    'tag'         => $tag,
                    'isInstalled' => $isInstalled
                ];
            }
        }

        $this->sections[] = [
            'title'  => $this->translator->trans('theme_assistant.content-packages.title', [], 'contao_default'),
            'module' => $this->twig->render(
                '@ContaoThemeManagerBridge/content-package.html.twig',
                [
                    'files' => $packages ?? null,
                    'label'    => [
                        'export'  => $this->translator->trans('theme_assistant.label.export', [], 'contao_default'),
                        'store'   => $this->translator->trans('theme_assistant.label.content-store', [], 'contao_default'),
                        'empty'   => $this->translator->trans('theme_assistant.content-package.label.empty', [], 'contao_default')
                    ],
                    'link'     => [
                        'store'   => $this->translator->trans('theme_assistant.link.store', [], 'contao_default')
                    ]
                ]
            )
        ];
    }

    private function readThemeManifest(): ?array
    {
        $root = Controller::getContainer()->getParameter('kernel.project_dir');

        $finder = new Finder();
        $finder->files()->name('theme.manifest.json')->in($root);

        if($finder->hasResults())
        {
            foreach ($finder as $file)
            {
                return array_merge(json_decode($file->getContents(), true), [
                    'realPath' => $file->getRealPath(),
                    'basename' => $file->getBasename(),
                    'path'     => $file->getPath()
                ]);
            }
        }

        return null;
    }

    private function getContentPackages(): ?Finder
    {
        $root = Controller::getContainer()->getParameter('kernel.project_dir');

        $finder = new Finder();
        $finder->files()->name('*.content')->in($root);

        if(!$finder->hasResults())
        {
            return null;
        }

        return $finder;
    }
}
