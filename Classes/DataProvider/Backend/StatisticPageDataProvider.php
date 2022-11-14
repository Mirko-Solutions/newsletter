<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StatisticPageDataProvider implements BackendDataProvider
{
    private NewsletterRepository $newsletterRepository;

    public function __construct(
        NewsletterRepository $newsletterRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
    }

    public function getPageData($pid): array
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Libraries/Grid');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Module/Statistics/Statistics');
        /**
         * @var Newsletter $newsletters
         */
        $newsletters = $this->newsletterRepository->getLatest($pid) ?? new Newsletter();

        return [
            'status' => $newsletters->getStatus(),
            'validationResult' => $newsletters->getValidatedContent()
        ];
    }
}