<?php

namespace Oveleon\ContaoThemeManagerBridge\Export;

use Contao\Controller;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DC_Table;
use Contao\Theme;
use Contao\ZipWriter;

class ContentPackageExport
{
    /**
     * @Hook("exportTheme")
     */
    public function extend(\DOMDocument $xml, ZipWriter $zipArchive, int $themeId): void
    {
        # So far, the following areas are exported:
        # - tl_theme
        # - tl_style_sheet
        # - tl_style
        # - tl_image_size
        # - tl_image_size_item
        # - tl_module
        # - tl_layout
        # - tl_files

        # ToDo: Export
        # - tl_page
        # - tl_article
        # - tl_content
        # - tl_form
        # - tl_form_field
        # - tl_user
        # - tl_member
        # - tl_user_group
        # - tl_member_group

        # Extra bundles
        # - tl_faq
        # - tl_faq_category
        # - tl_news
        # - tl_calendar
        # - tl_calendar_events
        # - tl_calendar_feed
        # - tl_comments
        # - tl_comments_notify
        # - tl_newsletter
        # - tl_newsletter_channel
        # - tl_newsletter_deny_list
        # - tl_newsletter_recipients

        $test = '';
    }

    public function export(int $themeId): void
    {
        Controller::loadDataContainer('tl_theme');

        $dataContainer = new DC_Table('tl_theme');
        $dataContainer->id = $themeId;

        (new Theme())->exportTheme($dataContainer);
    }
}
