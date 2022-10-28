<?php

namespace Oveleon\ContaoThemeManagerBridge\Export;

use Contao\ArticleModel;
use Contao\CalendarEventsModel;
use Contao\CalendarFeedModel;
use Contao\CalendarModel;
use Contao\CommentsModel;
use Contao\CommentsNotifyModel;
use Contao\ContentModel;
use Contao\Database;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\File;
use Contao\FilesModel;
use Contao\FormFieldModel;
use Contao\FormModel;
use Contao\ImageSizeItemModel;
use Contao\ImageSizeModel;
use Contao\LayoutModel;
use Contao\MemberGroupModel;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\NewsArchiveModel;
use Contao\NewsletterChannelModel;
use Contao\NewsletterDenyListModel;
use Contao\NewsletterModel;
use Contao\NewsletterRecipientsModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StyleModel;
use Contao\StyleSheetModel;
use Contao\ThemeModel;
use Contao\UserGroupModel;
use Contao\ZipWriter;
use Exception;
use Symfony\Component\Finder\Finder;

class ContentPackageExport
{
    const ARCHIVE_FILE_EXTENSION = '.content';
    const TABLE_FILE_EXTENSION = '.table';

    protected ZipWriter $archive;

    protected ?array  $manifest = null;
    protected ?string $filename = null;
    protected ?string $filepath = null;
    protected ?array  $directories = null;

    /**
     * Set the file name
     */
    public function setFileName(string $name): self
    {
        $this->filename = $name;

        return $this;
    }

    /**
     * Set manifest data
     */
    public function setManifestData(array $data): self
    {
        if(null === $this->manifest)
        {
            $this->manifest = $data;
        }

        $this->manifest = [...$this->manifest, ...$data];

        return $this;
    }

    /**
     * Adds a directory to export
     */
    public function addDirectory(string $path): self
    {
        if(!$path)
        {
            return $this;
        }

        if(!is_array($this->directories))
        {
            $this->directories = [];
        }

        $this->directories[] = $path;

        // Add directories to manifest
        $this->setManifestData([
            'directories' => array_map(fn($dir) => $this->getDirectoryRoot($dir), $this->directories)
        ]);

        return $this;
    }

    /**
     * Send the exported file to the browser
     *
     * @throws Exception
     */
    public function sendToBrowser(?string $filename = null): void
    {
        if(!$this->filename || !$this->filepath)
        {
            throw new Exception('The export method must be executed before getFilePath is called.');
        }

        (new File($this->getFilePath()))->sendToBrowser($filename ?? '');
    }

    /**
     * Return the file path
     *
     * @throws Exception
     */
    public function getFilePath(): string
    {
        if(!$this->filename || !$this->filepath)
        {
            throw new Exception('The export method must be executed before getFilePath is called.');
        }

        return $this->filepath . $this->filename . self::ARCHIVE_FILE_EXTENSION;
    }

    /**
     * Starts the export
     *
     * @throws Exception
     */
    public function export(int $themeId, int $rootPageId): self
    {
        // Set file props
        $this->filename = $this->filename ?? 'export';
        $this->filepath = 'system/tmp/';

        // Create zip archive
        $this->archive = new ZipWriter($this->getFilePath());

        // Create and export content.manifest.json
        $this->exportContentManifest();

        // Export pages
        $pageIds = $this->exportPages($rootPageId);

        // Export articles
        $articleIds = $this->exportMany(ArticleModel::class, $pageIds);

        // Export content elements
        $this->exportMany(ContentModel::class, $articleIds);

        // Export theme and sub tables
        $this->exportOne(ThemeModel::class, $themeId);
        $this->exportMany(StyleSheetModel::class, [$themeId]);
        $this->exportMany(StyleModel::class, [$themeId]);
        $this->exportMany(ImageSizeModel::class, [$themeId]);
        $this->exportMany(ImageSizeItemModel::class, [$themeId]);
        $this->exportMany(ModuleModel::class, [$themeId]);
        $this->exportMany(LayoutModel::class, [$themeId]);
        $this->exportMany(FilesModel::class, [$themeId]);

        // Export other tables
        $this->exportMany(FormModel::class);
        $this->exportMany(FormFieldModel::class);
        $this->exportMany(UserGroupModel::class);
        $this->exportMany(MemberGroupModel::class);
        $this->exportMany(FaqModel::class);
        $this->exportMany(FaqCategoryModel::class);
        $this->exportMany(NewsModel::class);
        $this->exportMany(NewsArchiveModel::class);
        $this->exportMany(CalendarModel::class);
        $this->exportMany(CalendarEventsModel::class);
        $this->exportMany(CalendarFeedModel::class);
        $this->exportMany(CommentsModel::class);
        $this->exportMany(CommentsNotifyModel::class);
        $this->exportMany(NewsletterModel::class);
        $this->exportMany(NewsletterChannelModel::class);
        $this->exportMany(NewsletterDenyListModel::class);
        $this->exportMany(NewsletterRecipientsModel::class);

        // Export directories
        $this->exportDirectories();

        // Close archive
        $this->archive->close();

        return $this;
    }

    /**
     * Export the content manifest
     */
    protected function exportContentManifest(): void
    {
        $this->archive->addString(json_encode($this->manifest), 'content.manifest.json');
    }

    /**
     * Export pages by a given parent id
     */
    protected function exportPages(int $parentId): array
    {
        // Collect page ids by root page
        $pageIds = [
            $parentId,
            ... Database::getInstance()->getChildRecords($parentId, 'tl_page')
        ];

        // Get page models by ids
        $pages = PageModel::findMultipleByIds($pageIds);

        // Creates a file of the model / the collection
        $this->addCollectionFile($pages, 'tl_page');

        // Return page ids
        return $pageIds;
    }

    /**
     * Export one data row by a specific model, table and id
     */
    protected function exportOne($modelClass, int $id): void
    {
        // Get model by id
        $model = $modelClass::findById($id);
        $table = $modelClass::getTable();

        // Create collection
        $collection = new Collection([$model], $table);

        // Add collection to the archive
        $this->addCollectionFile($collection, $table);
    }

    /**
     * Export data by a specific model, table and optionally parent ids
     */
    protected function exportMany($modelClass, array|bool|null $parentIds = false): ?array
    {
        if(null === $parentIds || !class_exists($modelClass))
        {
            return null;
        }

        $modelMethod = match ($parentIds) {
            false   => 'findAll',
            default => 'findByPid'
        };

        $table = $modelClass::getTable();
        $collection = null;

        foreach ($parentIds ?: [[]] as $parentId)
        {
            if($model = $modelClass::$modelMethod($parentId))
            {
                if($model instanceof Collection)
                {
                    foreach ($model->getModels() as $mod)
                    {
                        $collection[] = $mod->current();
                    }

                    continue;
                }

                $collection[] = $model->current();
            }
        }

        if(!$collection)
        {
            return null;
        }

        // Create collection
        $collection = new Collection($collection, $table);

        // Add collection to the archive
        $this->addCollectionFile($collection, $table);

        // Return ids
        return $collection->fetchEach('id');
    }

    /**
     * @throws Exception
     */
    protected function exportDirectories(): void
    {
        if(!is_array($this->directories))
        {
            return;
        }

        foreach ($this->directories as $directory)
        {
            $directoryFinder = new Finder();
            $directoryFinder
                ->directories()
                ->in($directory)
                ->depth("==0");

            if(!$directoryFinder->hasResults())
            {
                continue;
            }

            $rootDirectory = $this->getDirectoryRoot($directory);

            foreach ($directoryFinder as $dir)
            {
                $fileFinder = new Finder();
                $fileFinder
                    ->files()
                    ->in($dir->getRealPath());

                if(!$fileFinder->hasResults())
                {
                    continue;
                }

                $directoryPath = $rootDirectory . DIRECTORY_SEPARATOR . $dir->getRelativePathname();

                foreach ($fileFinder as $file)
                {
                    $filePath = $directoryPath . DIRECTORY_SEPARATOR . $file->getRelativePathname();

                    $this->archive->addFile($filePath);
                }
            }
        }
    }

    /**
     * Creates a file of a collection and add it to the zip archive
     */
    protected function addCollectionFile(?Collection $modelCollection, string $fileName): void
    {
        if(null === $modelCollection)
        {
            return;
        }

        $this->archive->addString(
            json_encode($modelCollection->fetchAll(), JSON_INVALID_UTF8_IGNORE),
            $fileName . self::TABLE_FILE_EXTENSION
        );
    }

    protected function getDirectoryRoot($directory): string
    {
        return 'files' . DIRECTORY_SEPARATOR . basename($directory);
    }
}
