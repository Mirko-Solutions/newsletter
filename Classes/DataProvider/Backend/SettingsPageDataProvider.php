<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\BounceAccountRepository;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Utility\PlainConverterRegistration;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SettingsPageDataProvider implements BackendDataProvider
{
    private NewsletterRepository $newsletterRepository;

    private BounceAccountRepository $bounceAccountRepository;

    public function __construct(
        NewsletterRepository $newsletterRepository,
        BounceAccountRepository $bounceAccountRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->bounceAccountRepository = $bounceAccountRepository;
    }

    public function getPageData($pid): array
    {
        /**
         * @var Newsletter $newsletters
         */
        $newsletter = $this->newsletterRepository->getLatest($pid) ?? new Newsletter();

        $repetition = [
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_none',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_daily',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_weekly',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_biweekly',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_monthly',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_quarterly',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_semiyearly',
                'newsletter'
            ),
            LocalizationUtility::translate(
                'LLL:EXT:newsletter/Resources/Private/Language/locallang_db.xlf:tx_newsletter_domain_model_newsletter.repetition_yearly',
                'newsletter'
            )
        ];
        $bounceAccounts = $this->bounceAccountRepository->findAll()->toArray();

        return [
            'newsletter' => $newsletter,
            'repetition' => $repetition,
            'bounceAccounts' =>  $bounceAccounts,
            'plainConverters' => PlainConverterRegistration::getPlainConvertersList()
        ];
    }
}