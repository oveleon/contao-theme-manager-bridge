<?php

namespace Oveleon\ContaoThemeManagerBridge\Controller\BackendModule;

use Contao\Controller;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\Message;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\PageModel;
use Contao\ThemeModel;
use Contao\ZipReader;
use Exception;
use Oveleon\ContaoThemeManagerBridge\Controller\ContentPackageExportController;
use Oveleon\ContaoThemeManagerBridge\Controller\ContentPackageImportController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Routing\Annotation\Route;

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
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
        private readonly RouterInterface $router,
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
                    'headline'          => $this->translator->trans('theme_assistant.headline', [], 'contao_default'),
                    'backLabel'         => $this->translator->trans('MSC.backBT', [], 'contao_default'),
                    'backTitle'         => $this->translator->trans('MSC.backBTTitle', [], 'contao_default'),
                    'contentExport'     => $this->translator->trans('theme_assistant.content-package.label.export', [], 'contao_default'),
                    'contentExportDesc' => $this->translator->trans('theme_assistant.content-package.label.exportDescription', [], 'contao_default'),
                    'contentStore'      => $this->translator->trans('theme_assistant.label.content-store', [], 'contao_default'),
                    'theme'             => $this->translator->trans('theme_assistant.label.theme', [], 'contao_default'),
                    'pageEntry'         => $this->translator->trans('theme_assistant.label.pageEntry', [], 'contao_default'),
                    'name'              => $this->translator->trans('theme_assistant.label.name', [], 'contao_default'),
                    'version'           => $this->translator->trans('theme_assistant.label.version', [], 'contao_default'),
                    'directory'         => $this->translator->trans('theme_assistant.label.directory', [], 'contao_default'),
                    'exportHl'          => $this->translator->trans('theme_assistant.content-package.label.export', [], 'contao_default')
                ],
                'action' => [
                    'back'          => 'contao?do=themes',
                    'store'         => $this->translator->trans('theme_assistant.link.store', [], 'contao_default'),
                    'contentExport' => $this->router->generate(ContentPackageExportController::class)
                ],
                'rt'          => $this->csrfTokenManager->getDefaultTokenValue(),
                'themes'      => $this->getThemes(),
                'pages'       => $this->getRootPages(),
                'directories' => $this->getFileDirectories(),
                'messages'    => Message::generate(),
                'sections'    => $this->sections
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
                ->setSize([0, 50])
                ->fromPath($manifest['path'] . '/' . $manifest['logo'])
                ->buildIfResourceExists();

            if($figure)
            {
                $template = new FrontendTemplate('image');
                $figure->applyLegacyTemplateData($template);

                $logo = $template->parse();
            }
        }

        $this->sections[] = [
            'title'  => $this->translator->trans('theme_assistant.theme.title', [], 'contao_default'),
            'module' => $this->twig->render(
                '@ContaoThemeManagerBridge/theme.html.twig',
                [
                    'manifest' => $manifest,
                    'logo'     => $logo ?? null,
                    'label'    => [
                        'version' => $this->translator->trans('theme_assistant.theme.label.version', [], 'contao_default'),
                        'docs'    => $this->translator->trans('theme_assistant.label.docs', [], 'contao_default'),
                        'info'    => $this->translator->trans('theme_assistant.label.info', [], 'contao_default'),
                        'store'   => $this->translator->trans('theme_assistant.label.store', [], 'contao_default'),
                        'empty'   => $this->translator->trans('theme_assistant.theme.label.empty', [], 'contao_default'),
                        'export'  => $this->translator->trans('theme_assistant.label.export', [], 'contao_default')
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
        $this->sections[] = [
            'title'  => $this->translator->trans('theme_assistant.content-package.title', [], 'contao_default'),
            'module' => $this->twig->render(
                '@ContaoThemeManagerBridge/content-package.html.twig',
                [
                    'files'  => $this->getContentPackages(),
                    'label'  => [
                        'version'    => $this->translator->trans('theme_assistant.theme.label.version', [], 'contao_default'),
                        'import'     => $this->translator->trans('theme_assistant.label.import', [], 'contao_default'),
                        'store'      => $this->translator->trans('theme_assistant.label.content-store', [], 'contao_default'),
                        'empty'      => $this->translator->trans('theme_assistant.content-package.label.empty', [], 'contao_default'),
                        'importDesc' => $this->translator->trans('theme_assistant.content-package.label.importDescription', [], 'contao_default'),
                        'pageEntry'  => $this->translator->trans('theme_assistant.label.pageEntry', [], 'contao_default'),
                        'createRoot' => $this->translator->trans('theme_assistant.label.createRoot', [], 'contao_default'),
                    ],
                    'action' => [
                        'store'         => $this->translator->trans('theme_assistant.link.store', [], 'contao_default'),
                        'contentImport' => $this->router->generate(ContentPackageImportController::class)
                    ],
                    'pages'  => $this->getRootPages(),
                    'rt'     => $this->csrfTokenManager->getDefaultTokenValue(),
                ]
            )
        ];
    }

    /**
     * Returns the theme manifest
     */
    private function readThemeManifest(): ?array
    {
        $root = Controller::getContainer()->getParameter('kernel.project_dir');

        $finder = new Finder();
        $finder
            ->files()
            ->name('theme.manifest.json')
            ->in($root);

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

    private function getThemes(): array
    {
        if(!$themes = ThemeModel::findAll())
        {
            return [];
        }

        return array_combine(
            $themes->fetchEach('id') ?? [],
            $themes->fetchEach('name') ?? []
        );
    }

    private function getFileDirectories(): array
    {
        $root = Controller::getContainer()->getParameter('kernel.project_dir');
        $path  = 'files';

        $finder = new Finder();
        $finder
            ->directories()
            ->in($root . DIRECTORY_SEPARATOR . $path)
            ->depth('== 0');

        $dirs = [];

        if ($finder->hasResults())
        {
            foreach ($finder as $dir)
            {
                $dirs[$dir->getRealPath()] = $dir->getBasename();
            }
        }

        return $dirs;
    }

    private function getRootPages(): array
    {
        if(!$pages = PageModel::findByType('root'))
        {
            return [];
        }

        return array_combine(
            $pages->fetchEach('id') ?? [],
            $pages->fetchEach('title') ?? []
        );
    }

    /**
     * Returns content packages with extra information
     *
     * @throws Exception
     */
    private function getContentPackages(): ?array
    {
        $root = Controller::getContainer()->getParameter('kernel.project_dir');

        $finder = new Finder();
        $finder
            ->files()
            ->name('*.content')
            ->in($root)
            ->exclude('system');

        if(!$finder->hasResults())
        {
            return null;
        }

        $packages = null;

        foreach ($finder as $contentPackage)
        {
            // Check found packages and enrich them with information
            $archive = new ZipReader($contentPackage->getRelativePathname());

            // Get all files in archive
            $fileList = $archive->getFileList();

            // Check if a manifest exists, otherwise skip
            if(!in_array('content.manifest.json', $fileList))
            {
                continue;
            }

            // Set pointer to manifest file
            if(!$archive->getFile('content.manifest.json'))
            {
                throw new Exception('The manifest file cannot be determined.');
            }

            // Read manifest file
            $manifest = json_decode($archive->unzip(), true);

            // Try to get a logo if one was supplied
            if($archive->getFile('logo.png'))
            {
                $file = new File('system/tmp/'. substr(md5(mt_rand()), 0, 7) . '.png');
                $file->write($archive->unzip());
                $file->close();

                $figure = $this->studio
                    ->createFigureBuilder()
                    ->setSize([0, 60])
                    ->fromPath($file->path)
                    ->buildIfResourceExists();

                if($figure)
                {
                    $template = new FrontendTemplate('image');
                    $figure->applyLegacyTemplateData($template);

                    $logo = $template->parse();
                }
            }

            $packages[] = [
                'name'     => $contentPackage->getFilenameWithoutExtension(),
                'realPath' => $contentPackage->getRealPath(),
                'path'     => $contentPackage->getPath(),
                'manifest' => $manifest,
                'logo'     => $logo ?? null
            ];
        }

        return $packages;
    }
}
