<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// ========== Register BE Modules
if (TYPO3_MODE == 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Mirko.newsletter',
        'web', // Make newsletter module a submodule of 'user'
        'tx_newsletter_m1', // Submodule key
        'before:info', // Position
        [
            \Mirko\Newsletter\Controller\ModuleController::class => 'newsletter, statistics',
            \Mirko\Newsletter\Controller\NewsletterController::class => 'list, listPlanned, create, statistics',
            \Mirko\Newsletter\Controller\EmailController::class => 'list',
            \Mirko\Newsletter\Controller\LinkController::class => 'list',
            \Mirko\Newsletter\Controller\BounceAccountController::class => 'list',
            \Mirko\Newsletter\Controller\RecipientListController::class => 'list, listRecipient',
        ],
        [
            'access' => 'user,group',
            'icon' => 'EXT:newsletter/Resources/Public/Icons/tx_newsletter.svg',
            'labels' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_module.xlf',
        ]
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsletter_domain_model_newsletter', 'EXT:newsletter/Resources/Private/Language/locallang_csh_tx_newsletter_domain_model_newsletter.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_newsletter_domain_model_newsletter');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsletter_domain_model_bounceaccount', 'EXT:newsletter/Resources/Private/Language/locallang_csh_tx_newsletter_domain_model_bounceaccount.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_newsletter_domain_model_bounceaccount');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsletter_domain_model_recipientlist', 'EXT:newsletter/Resources/Private/Language/locallang_csh_tx_newsletter_domain_model_recipientlist.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_newsletter_domain_model_recipientlist');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsletter_domain_model_email', 'EXT:newsletter/Resources/Private/Language/locallang_csh_tx_newsletter_domain_model_email.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_newsletter_domain_model_email');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsletter_domain_model_link', 'EXT:newsletter/Resources/Private/Language/locallang_csh_tx_newsletter_domain_model_link.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_newsletter_domain_model_link');
