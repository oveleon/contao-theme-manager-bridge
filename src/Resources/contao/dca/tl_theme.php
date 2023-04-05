<?php

use Contao\ArrayUtil;

// Add global operations
ArrayUtil::arrayInsert($GLOBALS['TL_DCA']['tl_theme']['list']['global_operations'], -1, [
    'theme_assistant' => [
        'href' => '',
        'icon' => 'bundles/contaothememanagerbridge/icons/assistant.svg'
    ]
]);

$GLOBALS['TL_DCA']['tl_theme']['fields']['contentPackage'] = [
    'sql' => "blob NULL"
];
