<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Newsletter',
    'description' => 'Send any pages as Newsletter and provide statistics on opened emails and clicked links.',
    'category' => 'module',
    'version' => '5.0.4',
    'state' => 'stable',
    'uploadfolder' => 1,
    'author' => 'Mirko (developer: Mirko)',
    'author_email' => 'support@mirko.in.ua',
    'author_company' => 'Mirko',
    'constraints' => [
        'depends' => [
            'php' => '7.4.*-8.1.*',
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
];
