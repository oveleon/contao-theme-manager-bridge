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

    protected ZipReader $archive;

    protected ?array $manifest = null;
    protected ?int $rootPage = null;

    /**
     * Sets a root page in which new pages are to be imported
     */
    public function setRootPage(int $pageId): void
    {
        $this->rootPage = $pageId;
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

        /**
         * ToDo:
         * - (Register callbacks based on tables or file names (CALLBACK_MODEL, CALLBACK_ON_FINISH) and handle them in import methods (e.g. see Line 92))
         *   - (Create hook to register "callbackRules" before starting import)
         * - (Collect IDs based on table globally to retrieve them in the method `importNestedTable`)
         * - Create callback to overwrite jumpTo in News Archives
         * - Import files and manifest
         */

        // Import theme and child tables
        $themeIds         = $this->importTable('tl_theme');
        $styleSheetIds    = $this->importNestedTable('tl_style_sheet', $themeIds);
        $styleIds         = $this->importNestedTable('tl_style', $styleSheetIds);
        $imageSizeIds     = $this->importNestedTable('tl_image_size', $themeIds);
        $imageSizeItemIds = $this->importNestedTable('tl_image_size_item', $imageSizeIds);
        $moduleIds        = $this->importNestedTable('tl_module', $themeIds);
        $layoutIds        = $this->importNestedTable('tl_layout', $themeIds);

        // Import other tables
        $userGroupIds     = $this->importTable('tl_user_group');
        $memberGroupIds   = $this->importTable('tl_member_group');

        $faqCategoryIds   = $this->importTable('tl_faq_category');
        $faqIds           = $this->importNestedTable('tl_faq', $faqCategoryIds);

        $newsArchiveIds   = $this->importTable('tl_news_archive'); // ToDo: Add callback -> overwrite jumpTo
        $newsFeedIds      = $this->importTable('tl_news_feed');
        $newsIds          = $this->importNestedTable('tl_news', $newsArchiveIds);

        $calendarIds      = $this->importTable('tl_calendar');
        $calendarFeedIds  = $this->importTable('tl_calendar_feed');
        $calendarEventIds = $this->importNestedTable('tl_calendar_events', $calendarIds);

        $commentIds       = $this->importTable('tl_comments');
        $commentNotifyIds = $this->importTable('tl_comments_notify');

        $nlChannelIds     = $this->importTable('tl_newsletter_channel');
        $nlDenyListIds    = $this->importTable('tl_newsletter_deny_list');
        $nlIds            = $this->importNestedTable('tl_newsletter', $nlChannelIds);
        $nlRecipientsIds  = $this->importNestedTable('tl_newsletter_recipients', $nlChannelIds);

        $formIds          = $this->importTable('tl_form');
        $formFieldIds     = $this->importNestedTable('tl_form_field', $formIds);

        // Import pages, articles and content elements
        $pageIds          = $this->importTreeTable('tl_page', $this->rootPage, fn($model) => $this->overwritePageLayout($model, $layoutIds));
        $articleIds       = $this->importNestedTable('tl_article', $pageIds);

        $articleContentIds  = $this->importNestedTable('tl_content.tl_article', $articleIds, fn($model) => $this->overwriteContentElement($model, $articleIds, $formIds, $moduleIds));
        $newsContentIds     = $this->importNestedTable('tl_content.tl_news', $newsIds, fn($model) => $this->overwriteContentElement($model, $articleIds, $formIds, $moduleIds));
        $calendarContentIds = $this->importNestedTable('tl_content.tl_calendar_events', $newsIds, fn($model) => $this->overwriteContentElement($model, $articleIds, $formIds, $moduleIds));

        // Overwrite connection ids for content elements of type alias
        $this->overwriteAliasContentElement($articleContentIds);
        $this->overwriteAliasContentElement($newsContentIds);
        $this->overwriteAliasContentElement($calendarContentIds);

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
     * Import table and return a new collection of ids
     *
     * @throws Exception
     */
    protected function importTable(string $filename, ?callable $modelCallback = null): array
    {
        // Get table content
        if(!$tableContent = $this->unzipFileContent($filename))
        {
            return [];
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

            if($modelCallback)
            {
                call_user_func($modelCallback, $model);
            }

            // Save model and set new id to collection
            $idCollection[ $id ] = ($model->save())->id;
        }

        return $idCollection;
    }

    /**
     * Import table based on a parent table
     *
     * @throws Exception
     */
    protected function importNestedTable(string $filename, array $parentIds, ?callable $modelCallback = null): array
    {
        // Get table content
        if(!($tableContent = $this->unzipFileContent($filename)) || !count($parentIds))
        {
            return [];
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

            if($modelCallback)
            {
                call_user_func($modelCallback, $model);
            }

            // Save model and set new id to collection
            $idCollection[ $id ] = ($model->save())->id;
        }

        return $idCollection;
    }

    /**
     * Import tables from mode DataContainer::MODE_TREE
     *
     * @throws Exception
     */
    protected function importTreeTable(string $table, ?int $parentId = null, ?callable $modelCallback = null): array
    {
        // Get table content
        if(!$tableContent = $this->unzipFileContent($table))
        {
            return [];
        }

        // Group rows by pid
        $groups = $this->group($tableContent[$table]);

        // Get model class by table
        $modelClass = Model::getClassFromTable($table);

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

                if($modelCallback)
                {
                    call_user_func($modelCallback, $model);
                }

                // Save model and set new id to collection
                $idCollection[ $id ] = ($model->save())->id;
            }
        }

        return $idCollection;
    }

    protected function importFile(): void
    {
        //$file = $this->archive->unzip();
    }

    /**
     * Returns the model based on a table with table name verification
     */
    protected function getClassFromFileName(string $table): string
    {
        return Model::getClassFromTable(strtok($table, '.'));
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
    private function overwritePageLayout($model, ?array $layoutIds): void
    {
        /** @var PageModel $model */
        if($model->includeLayout)
        {
            $model->layout = $layoutIds[ $model->layout ] ?? 0;
        }
    }

    /**
     * Overwrites connected ids in a content element
     */
    private function overwriteContentElement($model, ?array $articleIds, ?array $formIds, ?array $moduleIds): void
    {
        /** @var ContentModel $model */
        switch($model->type)
        {
            case 'article':
                $model->articleAlias = $articleIds[ $model->articleAlias ] ?? 0;
                break;

            case 'form':
                $model->form = $formIds[ $model->form ] ?? 0;
                break;

            case 'module':
                $model->module = $moduleIds[ $model->module ] ?? 0;
                break;

            case 'teaser':
                $model->article = $articleIds[ $model->article ] ?? 0;
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
