<?php

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'PIXELINK.' . $_EXTKEY,
        'user',          // Main area
        'solrstatics',         // Name of the module
        '',             // Position of the module
        array(          // Allowed controller action combinations
            'Search' => 'index,export,error',
        ),
        array(          // Additional configuration
            'access'    => 'user,group',
            'icon'      => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
            'labels'    => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xml',
        )
    );
}



if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Solr Statics');
