<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Repository\RecipientListRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SendingPageDataProvider implements BackendDataProvider
{
    private RecipientListRepository $recipientListRepository;

    public function __construct(
        RecipientListRepository $recipientListRepository
    ) {
        $this->recipientListRepository = $recipientListRepository;
    }

    public function getPageData($pid): array
    {
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Newsletter/Module/Newsletter/Sending');

        return [
            'recipientList' => $this->recipientListRepository->findAll()
        ];
    }
}