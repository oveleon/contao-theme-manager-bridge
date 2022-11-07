<?php

namespace Oveleon\ContaoThemeManagerBridge\Import;

use Contao\ContentModel;
use Contao\Controller;
use Contao\Model;
use Contao\PageModel;
use Contao\ZipReader;
use Exception;
use Oveleon\ContaoThemeManagerBridge\Export\ContentPackageExport;

class ContentPackageImport
{
    const FOREIGN_KEY = 'pid';

    const CALLBACK_EACH_MODULE = 'module';      // Parameter: $model
    const CALLBACK_ON_FINISHED = 'finished';    // Parameter: $idConnections

    protected ZipReader $archive;

    protected ?array $manifest = null;
    protected ?int $rootPage = null;

    protected array $connections = [];
    protected ?array $callbacks = null;

    /**
     * Sets a root page in which new pages are to be imported
     */
    public function setRootPage(int $pageId): void
    {
        $this->rootPage = $pageId;
    }

    /**
     * Register a callback
     */
    public function registerCallback(string $callbackMode, string $filename, callable $callback): void
    {
        if(null === $this->callbacks)
        {
            $this->callbacks = [
                self::CALLBACK_EACH_MODULE => [],
                self::CALLBACK_ON_FINISHED => []
            ];
        }

        if(array_key_exists($filename, $this->callbacks[$callbackMode]))
        {
            $this->callbacks[$callbackMode][$filename][] = $callback;
        }
        else
        {
            $this->callbacks[$callbackMode][$filename] = [$callback];
        }
    }

    /**
     * Starts the import
     *
     * @throws Exception
     */
    public function import(string $filepath): self
    {
        $root = Controller::getContainer()->getParameter('kernel.project_dir');

        // Read zip archive
        $this->archive = new ZipReader(str_replace($root, '', $filepath));

        // Read manifest
        if($this->archive->getFile('content.manifest.json'))
        {
            $this->manifest = json_decode($this->archive->unzip(), true);
            $this->archive->reset();
        }

        // Register default callbacks for contao tables
        $this->registerDefaultCallbacks();

        // Todo: Hook (Register Callbacks)

        /**
         * ToDo:
         * - Create callback to overwrite jumpTo in News Archives
         * - Import files, tl_files and manifest
         */

        // Import theme and child tables
        $this->importTable('tl_theme');
        $this->importNestedTable('tl_style_sheet', 'tl_theme');
        $this->importNestedTable('tl_style', 'tl_style_sheet');
        $this->importNestedTable('tl_image_size', 'tl_theme');
        $this->importNestedTable('tl_image_size_item', 'tl_image_size');
        $this->importNestedTable('tl_module', 'tl_theme');
        $this->importNestedTable('tl_layout', 'tl_theme');

        // Import other tables
        $this->importTable('tl_user_group');
        $this->importTable('tl_member_group');

        $this->importTable('tl_faq_category');
        $this->importNestedTable('tl_faq', 'tl_faq_category');

        $this->importTable('tl_news_archive'); // ToDo: Add callback -> overwrite jumpTo
        $this->importTable('tl_news_feed');
        $this->importNestedTable('tl_news', 'tl_news_archive');

        $this->importTable('tl_calendar');
        $this->importTable('tl_calendar_feed');
        $this->importNestedTable('tl_calendar_events', 'tl_calendar');

        $this->importTable('tl_comments');
        $this->importTable('tl_comments_notify');

        $this->importTable('tl_newsletter_channel');
        $this->importTable('tl_newsletter_deny_list');
        $this->importNestedTable('tl_newsletter', 'tl_newsletter_channel');
        $this->importNestedTable('tl_newsletter_recipients', 'tl_newsletter_channel');

        $this->importTable('tl_form');
        $this->importNestedTable('tl_form_field', 'tl_form');

        // Import pages, articles and content elements
        $this->importTreeTable('tl_page', $this->rootPage);
        $this->importNestedTable('tl_article', 'tl_page');

        $this->importNestedTable('tl_content.tl_article', 'tl_article');
        $this->importNestedTable('tl_content.tl_news', 'tl_news');
        $this->importNestedTable('tl_content.tl_calendar_events', 'tl_calendar_events');

        // Fixme: Get files from manifest (directories)?
        while($this->archive->next())
        {
            switch(pathinfo($this->archive->file_basename, PATHINFO_EXTENSION))
            {
                // Skip manifest and table files
                case 'json':
                case 'table':
                    break;

                // Import files
                default:
                    $this->importFile();
            }
        }

        return $this;
    }

    /**
     * Import table by filename and return a new collection of ids
     *
     * @throws Exception
     */
    protected function importTable(string $filename): void
    {
        // Get table content
        if(!$tableContent = $this->unzipFileContent($filename))
        {
            return;
        }

        // Get model class by table
        $modelClass = $this->getClassFromFileName($filename);

        // Collection of parent id connections
        $idCollection = [];

        foreach ($tableContent[$filename] as $row)
        {
            // Temporarily store the ID of the row and delete it before assigning it to the new model
            $id = $row['id'];
            unset($row['id']);

            $model = new $modelClass();
            $model->setRow($row);

            if($callbacks = $this->getCallbacks(self::CALLBACK_EACH_MODULE, $filename))
            {
                foreach ($callbacks as $callback)
                {
                    call_user_func($callback, $model);
                }
            }

            // Save model and set new id to collection
            $idCollection[ $id ] = ($model->save())->id;
        }

        $this->connections[ $filename ] = $idCollection;

        if($callbacks = $this->getCallbacks(self::CALLBACK_ON_FINISHED, $filename))
        {
            foreach ($callbacks as $callback)
            {
                call_user_func($callback, $idCollection);
            }
        }
    }

    /**
     * Import table by filename based on a parent table
     *
     * @throws Exception
     */
    protected function importNestedTable(string $filename, string $parentTable): void
    {
        // Get parent ids
        $parentIds = $this->connections[$parentTable] ?? null;

        // Get table content
        if(!($tableContent = $this->unzipFileContent($filename)) || null === $parentIds)
        {
            return;
        }

        // Get model class by table
        $modelClass = $this->getClassFromFileName($filename);

        // Collection of parent id connections
        $idCollection = [];

        foreach ($tableContent[$filename] as $row)
        {
            // Temporarily store the ID of the row and delete it before assigning it to the new model
            $id = $row['id'];
            unset($row['id']);

            /** @var PageModel $model */
            $model = new $modelClass();
            $model->setRow($row);

            // Determine parent
            $model->pid = $parentIds[$row[self::FOREIGN_KEY]];

            if($callbacks = $this->getCallbacks(self::CALLBACK_EACH_MODULE, $filename))
            {
                foreach ($callbacks as $callback)
                {
                    call_user_func($callback, $model);
                }
            }

            // Save model and set new id to collection
            $idCollection[ $id ] = ($model->save())->id;
        }

        $this->connections[ $filename ] = $idCollection;

        if($callbacks = $this->getCallbacks(self::CALLBACK_ON_FINISHED, $filename))
        {
            foreach ($callbacks as $callback)
            {
                call_user_func($callback, $idCollection);
            }
        }
    }

    /**
     * Import table by filename from mode DataContainer::MODE_TREE
     *
     * @throws Exception
     */
    protected function importTreeTable(string $filename, ?int $parentId = null): void
    {
        // Get table content
        if(!$tableContent = $this->unzipFileContent($filename))
        {
            return;
        }

        // Group rows by pid
        $groups = $this->group($tableContent[$filename]);

        // Get model class by table
        $modelClass = Model::getClassFromTable($filename);

        // Collection of parent id connections
        $idCollection = [];

        foreach ($groups as $pid => $rows)
        {
            $isRoot = !$pid;

            foreach ($rows as $row)
            {
                // Temporarily store the ID of the row and delete it before assigning it to the new model
                $id = $row['id'];
                unset($row['id']);

                /** @var PageModel $model */
                $model = new $modelClass();
                $model->setRow($row);

                // If a parent ID was passed, use it as parent ID for the root page
                if($isRoot && $parentId)
                {
                    $model->pid = $parentId;
                }
                // If it is not the root page, the new parent ID must be determined
                elseif(!$isRoot)
                {
                    $model->pid = $idCollection[$pid];
                }

                if($callbacks = $this->getCallbacks(self::CALLBACK_EACH_MODULE, $filename))
                {
                    foreach ($callbacks as $callback)
                    {
                        call_user_func($callback, $model);
                    }
                }

                // Save model and set new id to collection
                $idCollection[ $id ] = ($model->save())->id;
            }
        }

        $this->connections[ $filename ] = $idCollection;

        if($callbacks = $this->getCallbacks(self::CALLBACK_ON_FINISHED, $filename))
        {
            foreach ($callbacks as $callback)
            {
                call_user_func($callback, $idCollection);
            }
        }
    }

    protected function importFile(): void
    {
        //$file = $this->archive->unzip();
    }

    /**
     * Return all callbacks by callback mode and filename
     */
    protected function getCallbacks(string $callbackMode, string $filename): ?array
    {
        return $this->callbacks[$callbackMode][$filename] ?? null;
    }

    /**
     * Register default callbacks for contao tables
     */
    private function registerDefaultCallbacks(): void
    {
        // Handle layout connections for each page
        $this->registerCallback(self::CALLBACK_EACH_MODULE, 'tl_page', fn($model) => $this->overwritePageLayout($model));

        // Handle content element connections
        $this->registerCallback(self::CALLBACK_EACH_MODULE, 'tl_content.tl_article', fn($model) => $this->overwriteContentElement($model));
        $this->registerCallback(self::CALLBACK_EACH_MODULE, 'tl_content.tl_news', fn($model) => $this->overwriteContentElement($model));
        $this->registerCallback(self::CALLBACK_EACH_MODULE, 'tl_content.tl_calendar_events', fn($model) => $this->overwriteContentElement($model));

        // Overwrite connection ids for content elements of type alias
        $this->registerCallback(self::CALLBACK_ON_FINISHED, 'tl_content.tl_article', fn($isConnection) => $this->overwriteAliasContentElement($isConnection));
        $this->registerCallback(self::CALLBACK_ON_FINISHED, 'tl_content.tl_news', fn($isConnection) => $this->overwriteAliasContentElement($isConnection));
        $this->registerCallback(self::CALLBACK_ON_FINISHED, 'tl_content.tl_calendar_events', fn($isConnection) => $this->overwriteAliasContentElement($isConnection));
    }

    /**
     * Returns the model based on a filename with table name verification
     */
    protected function getClassFromFileName(string $filename): string
    {
        return Model::getClassFromTable($this->getTableFromFileName($filename));
    }

    /**
     * Returns the table based on a table with table name verification
     */
    protected function getTableFromFileName(string $filename): string
    {
        return strtok($filename, '.');
    }

    /**
     * Unzip tables and return its content
     *
     * @throws Exception
     */
    protected function unzipFileContent(string|array $filename): ?array
    {
        $fileContent = null;

        foreach ((array) $filename as $file)
        {
            if(!$this->archive->getFile($file . ContentPackageExport::TABLE_FILE_EXTENSION))
            {
                continue;
            }

            $fileContent[$file] = json_decode($this->archive->unzip(), true);
            $this->archive->reset();
        }

        return $fileContent;
    }

    /**
     * Groups an array by an identifier
     */
    protected function group($rows): array
    {
        $temp = [];

        foreach($rows as $row)
        {
            $identifierValue = $row[ self::FOREIGN_KEY ];

            if(!array_key_exists($identifierValue, $temp))
            {
                $temp[ $identifierValue ] = array();
            }

            $temp[$identifierValue][ $row['id'] ] = $row;
        }

        return $temp;
    }

    /**
     * Overwrites the layout id from a page
     */
    private function overwritePageLayout($model): void
    {
        /** @var PageModel $model */
        if($model->includeLayout && ($layoutIds = ($this->connections ?? null)))
        {
            $model->layout = $layoutIds[ $model->layout ] ?? 0;
        }
    }

    /**
     * Overwrites connected ids in a content element
     */
    private function overwriteContentElement($model): void
    {
        /** @var ContentModel $model */
        switch($model->type)
        {
            case 'article':
                $model->articleAlias = $this->connections['tl_article'][ $model->articleAlias ] ?? 0;
                break;

            case 'form':
                $model->form = $this->connections['tl_form'][ $model->form ] ?? 0;
                break;

            case 'module':
                $model->module = $this->connections['tl_module'][ $model->module ] ?? 0;
                break;

            case 'teaser':
                $model->article = $this->connections['tl_article'][ $model->article ] ?? 0;
                break;
        }
    }

    /**
     * Overwrites the connection from one content element to another (Include: Content Element)
     */
    private function overwriteAliasContentElement(?array $contentIds): void
    {
        if(null === $contentIds)
        {
            return;
        }

        if($models = ContentModel::findBy(["type=? AND id IN ('" . implode("', '", array_values($contentIds)) . "')"], ['alias']))
        {
            foreach ($models as $model)
            {
                $model->cteAlias = $contentIds[ $model->cteAlias ];
            }
        }
    }

}
