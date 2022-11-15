<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Service\NewsletterService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StatisticPageDataProvider implements BackendDataProvider
{
    private NewsletterRepository $newsletterRepository;

    private NewsletterService $newsletterService;

    public function __construct(
        NewsletterRepository $newsletterRepository,
        NewsletterService $newsletterService
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->newsletterService = $newsletterService;
    }

    public function getPageData($pid): array
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Libraries/Grid');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Module/Statistics/Statistics');
        /**
         * @var Newsletter $newsletter
         */
        $newsletter = $this->newsletterRepository->getLatest($pid) ?? new Newsletter();

        return [
            'status' => $this->newsletterService->getStatus($newsletter),
            'validationResult' => $newsletter->getValidatedContent()
        ];
    }
}