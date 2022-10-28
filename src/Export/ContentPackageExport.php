<?php

namespace Oveleon\ContaoThemeManagerBridge\Export;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\Database;
use Contao\File;
use Contao\FilesModel;
use Contao\ImageSizeItemModel;
use Contao\ImageSizeModel;
use Contao\LayoutModel;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StyleModel;
use Contao\StyleSheetModel;
use Contao\ThemeModel;
use Contao\ZipWriter;
use Exception;

class ContentPackageExport
{
    const ARCHIVE_FILE_EXTENSION = '.content';
    const TABLE_FILE_EXTENSION = '.table';

    protected ZipWriter $archive;

    protected ?array  $manifest = null;
    protected ?string $filename = null;
    protected ?string $filepath = null;

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
        $this->manifest = $data;

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

        // Create content.manifest.json
        $this->exportContentManifest();

        // Export theme and sub tables
        $this->exportOne(ThemeModel::class, 'tl_theme', $themeId);
        $this->exportMany(StyleSheetModel::class, 'tl_style_sheet', [$themeId]);
        $this->exportMany(StyleModel::class, 'tl_style', [$themeId]);
        $this->exportMany(ImageSizeModel::class, 'tl_image_size', [$themeId]);
        $this->exportMany(ImageSizeItemModel::class, 'tl_image_size_item', [$themeId]);
        $this->exportMany(ModuleModel::class, 'tl_module', [$themeId]);
        $this->exportMany(LayoutModel::class, 'tl_layout', [$themeId]);
        $this->exportMany(FilesModel::class, 'tl_files', [$themeId]);

        // ToDo: Export content files (files/content)

        // Export pages, articles and content elements
        $pageIds    = $this->exportPages($rootPageId);
        $articleIds = $this->exportMany(ArticleModel::class, 'tl_article', $pageIds);
        $contentIds = $this->exportMany(ContentModel::class, 'tl_content', $articleIds);

        // Export other tables
        // ToDo: Export tl_form
        // ToDo: Export tl_form_field
        // ToDo: Export tl_user_group
        // ToDo: Export tl_member_group
        // ToDo: Export tl_faq
        // ToDo: Export tl_faq_category
        // ToDo: Export tl_news
        // ToDo: Export tl_calendar
        // ToDo: Export tl_calendar_events
        // ToDo: Export tl_calendar_feed
        // ToDo: Export tl_comments
        // ToDo: Export tl_comments_notify
        // ToDo: Export tl_newsletter
        // ToDo: Export tl_newsletter_channel
        // ToDo: Export tl_newsletter_deny_list
        // ToDo: Export tl_newsletter_recipients

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
    protected function exportOne($modelClass, string $table, int $id): void
    {
        // Get model by id
        $model = $modelClass::findById($id);

        // Create collection
        $collection = new Collection([$model], $table);

        // Add collection to the archive
        $this->addCollectionFile($collection, $table);
    }

    /**
     * Export data by a specific model, table and optionally parent ids
     */
    protected function exportMany($modelClass, string $table, array|bool|null $parentIds = false): ?array
    {
        if(null === $parentIds)
        {
            return null;
        }

        $modelMethod = match ($parentIds) {
            false   => 'findAll',
            default => 'findByPid'
        };

        $collection = null;

        foreach ($parentIds as $parentId)
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
}
