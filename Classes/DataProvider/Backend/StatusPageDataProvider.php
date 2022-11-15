<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Service\NewsletterService;

class StatusPageDataProvider implements BackendDataProvider
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
        /**
         * @var Newsletter $newsletters
         */
        $newsletters = $this->newsletterRepository->getLatest($pid) ?? new Newsletter();

        return [
            'status' => $this->newsletterService->getStatus($newsletters),
            'validationResult' => $newsletters->getValidatedContent()
        ];
    }
}