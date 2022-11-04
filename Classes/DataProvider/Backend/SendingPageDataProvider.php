<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\BounceAccountRepository;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Domain\Repository\RecipientListRepository;
use Mirko\Newsletter\Utility\PlainConverterRegistration;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class SendingPageDataProvider implements BackendDataProvider
{
    private RecipientListRepository $recipientListRepository;

    private NewsletterRepository $newsletterRepository;

    private BounceAccountRepository $bounceAccountRepository;

    public function __construct(
        RecipientListRepository $recipientListRepository,
        NewsletterRepository $newsletterRepository,
        BounceAccountRepository $bounceAccountRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->recipientListRepository = $recipientListRepository;
        $this->bounceAccountRepository = $bounceAccountRepository;
    }

    public function getPageData($pid): array
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Module/Newsletter/Grid');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Module/Newsletter/Sending');
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
            'recipientList' => $this->recipientListRepository->findAll(),
            'newsletter' => $newsletter,
            'repetition' => $repetition,
            'bounceAccounts' =>  $bounceAccounts,
            'plainConverters' => PlainConverterRegistration::getPlainConvertersList()
        ];
    }
}