<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter',
        'label' => 'planned_time',
        'iconfile' => 'EXT:newsletter/Resources/Public/Icons/tx_newsletter_domain_model_newsletter.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'planned_time,begin_time,end_time,repetition,plain_converter,is_test,attachments,sender_name,sender_email,replyto_name,replyto_email,inject_open_spy,inject_links_spy,bounce_account,recipient_list',
    ],
    'types' => [
        '1' => ['showitem' => 'planned_time,begin_time,end_time,repetition,plain_converter,is_test,attachments,sender_name,sender_email,replyto_name,replyto_email,inject_open_spy,inject_links_spy,bounce_account,recipient_list'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'planned_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.planned_time',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'size' => 12,
                'eval' => 'datetime,required',
            ],
        ],
        'begin_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.begin_time',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'readOnly' => true,
                'eval' => 'datetime',
            ],
        ],
        'end_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.end_time',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'readOnly' => true,
                'eval' => 'datetime',
            ],
        ],
        'repetition' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_none', '0'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_daily', '1'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_weekly', '2'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_biweekly', '3'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_monthly', '4'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_quarterly', '5'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_semiyearly', '6'],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_yearly', '7'],
                ],
                'maxitems' => 1,
            ],
        ],
        'plain_converter' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.plain_converter',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.plain_converter_builtin', \Mirko\Newsletter\Domain\Model\PlainConverter\Builtin::class],
                    ['LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.plain_converter_lynx', \Mirko\Newsletter\Domain\Model\PlainConverter\Lynx::class],
                ],
                'maxitems' => 1,
            ],
        ],
        'is_test' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.is_test',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'attachments' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.attachments',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'attachments',
                [
                    'maxitems' => 10,
                    'minitems' => 0,
                    'max_size' => 500,
                    'internal_type' => 'folder',
                    'disallowed' => 'php,php3',
                    'uploadfolder' => 'uploads/tx_newsletter',
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;;imageoverlayPalette,
                                --palette--;;filePalette'
                            ],
                        ],
                    ],
                ],
            )
        ],
        'sender_name' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.sender_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'sender_email' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.sender_email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'replyto_name' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.replyto_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'replyto_email' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.replyto_email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'inject_open_spy' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.inject_open_spy',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'inject_links_spy' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.inject_links_spy',
            'config' => [
                'type' => 'check',
                'default' => 0,
            ],
        ],
        'bounce_account' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.bounce_account',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_newsletter_domain_model_bounceaccount',
                'items' => [['', 0]],
                'maxitems' => 1,
                'wizards' => [
                    'edit' => [
                        'type' => 'popup',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
                        'module' => [
                            'name' => 'wizard_edit',
                        ],
                    ],
                ],
            ],
        ],
        'recipient_list' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.recipient_list',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_newsletter_domain_model_recipientlist',
                'maxitems' => 1,
                'wizards' => [
                    'edit' => [
                        'type' => 'popup',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
                        'module' => [
                            'name' => 'wizard_edit',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
