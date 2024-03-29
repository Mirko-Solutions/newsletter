<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount',
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => 'EXT:newsletter/Resources/Public/Icons/tx_newsletter_domain_model_bounceaccount.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'email,server,protocol,port,username,password',
    ],
    'types' => [
        '1' => ['showitem' => 'email,protocol,server,port,username,password,config'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'email' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required',
            ],
        ],
        'server' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.server',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'protocol' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.protocol',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['POP3', 'pop3'],
                    ['IMAP', 'imap'],
                ],
            ],
        ],
        'port' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.port',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'int',
            ],
        ],
        'username' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.username',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'password' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.password',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => \Mirko\Newsletter\Tca\BounceAccountTca::class,
            ],
        ],
        'config' => [
            'label' => 'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_bounceaccount.config',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 8,
                'eval' => \Mirko\Newsletter\Tca\BounceAccountTca::class,
                'wrap' => 'off',
                'default' => "poll ###SERVER###\nproto ###PROTOCOL### \nport ###PORT###\nusername \"###USERNAME###\"\npassword \"###PASSWORD###\"\n",
            ],
        ],
    ],
];
