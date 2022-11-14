<?php
declare(strict_types=1);

return [
    Mirko\Newsletter\Domain\Model\RecipientList::class => [
        'subclasses' => [
            Mirko\Newsletter\Domain\Model\RecipientList\BeUsers::class,
            Mirko\Newsletter\Domain\Model\RecipientList\FeGroups::class,
            Mirko\Newsletter\Domain\Model\RecipientList\FePages::class,
            Mirko\Newsletter\Domain\Model\RecipientList\Sql::class,
            Mirko\Newsletter\Domain\Model\RecipientList\CsvFile::class,
            Mirko\Newsletter\Domain\Model\RecipientList\CsvList::class,
            Mirko\Newsletter\Domain\Model\RecipientList\CsvUrl::class,
            Mirko\Newsletter\Domain\Model\RecipientList\Html::class,
        ],
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\BeUsers::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\BeUsers',
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\FeGroups::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\FeGroups',
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\FePages::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\FePages',
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\Sql::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\Sql',
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\CsvFile::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\CsvFile',
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\CsvList::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\CsvList',
    ],
    Mirko\Newsletter\Domain\Model\RecipientList\Html::class => [
        'tableName' => 'tx_newsletter_domain_model_recipientlist',
        'recordType' => 'Mirko\Newsletter\Domain\Model\RecipientList\Html',
    ],
];
