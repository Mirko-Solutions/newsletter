<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email',
        'label' => 'recipient_address',
        'iconfile' => 'EXT:newsletter/Resources/Public/Icons/tx_newsletter_domain_model_email.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'begin_time,end_time,recipient_address,recipient_data,open_time,bounce_time,unsubscribed,newsletter',
    ],
    'types' => [
        '1' => ['showitem' => 'begin_time,end_time,recipient_address,recipient_data,open_time,bounce_time,unsubscribed,newsletter'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'begin_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.begin_time',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'readOnly' => true,
                'eval' => 'datetime',
            ],
        ],
        'end_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.end_time',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'readOnly' => true,
                'eval' => 'datetime',
            ],
        ],
        'recipient_address' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.recipient_address',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true,
                'eval' => 'trim,required',
            ],
        ],
        'recipient_data' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.recipient_data',
            'config' => [
                'type' => 'user',
                'renderType' => 'userEmailField',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'open_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.open_time',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'bounce_time' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.bounce_time',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'newsletter' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.newsletter',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_newsletter_domain_model_newsletter',
                'minitems' => 0,
                'maxitems' => 1,
                'appearance' => [
                    'collapse' => 0,
                    'levelLinksPosition' => 'bottom',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1,
                ],
            ],
        ],
        'unsubscribed' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.unsubscribed',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'auth_code' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_email.auth_code',
            'config' => [
                'type' => 'input',
                'size' => 32,
                'readOnly' => true,
                'eval' => 'trim,required',
            ],
        ],
    ],
];
