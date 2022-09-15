<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Includes typoscript files
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:newsletter/Configuration/TypoScript/setup.txt">'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptConstants(
    '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:newsletter/Configuration/TypoScript/constants.txt">'
);

// Register keys for CLI
$TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys']['newsletter_bounce'] = ['EXT:newsletter/cli/bounce.php', '_CLI_scheduler'];

// Configure FE plugin element
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Mirko.newsletter',
    'p',
    [// list of controller
        \Mirko\Newsletter\Controller\EmailController::class => 'show, opened',
        \Mirko\Newsletter\Controller\LinkController::class => 'clicked',
        \Mirko\Newsletter\Controller\RecipientListController::class => 'unsubscribe, export',
    ],
    [// non-cacheable controller
        \Mirko\Newsletter\Controller\EmailController::class => 'show, opened, unsubscribe',
        \Mirko\Newsletter\Controller\LinkController::class => 'clicked',
        \Mirko\Newsletter\Controller\RecipientListController::class => 'export',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Mirko\Newsletter\Task\SendEmails::class] = [
    'extension' => 'newsletter',
    'title' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang.xlf:task_send_emails_title',
    'description' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang.xlf:task_send_emails_description',
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Mirko\Newsletter\Task\FetchBounces::class] = [
    'extension' => 'newsletter',
    'title' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang.xlf:task_fetch_bounces_title',
    'description' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang.xlf:task_fetch_bounces_description',
];

// Configure TCA custom eval and hooks to manage on-the-fly (de)encryption from database
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\Mirko\Newsletter\Tca\BounceAccountTca::class] = 'EXT:newsletter/Classes/Tca/BounceAccountTca.php';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Mirko\Newsletter\Tca\BounceAccountDataProvider::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseEditRow::class,
    ],
];

// Make a call to update
if (TYPO3_MODE === 'BE') {
    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $dispatcher->connect(
        \TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
        'afterExtensionInstall',
        \Mirko\Newsletter\Update\Update::class,
        'afterExtensionInstall'
    );
}
