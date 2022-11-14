<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;

class StatusPageDataProvider implements BackendDataProvider
{
    private NewsletterRepository $newsletterRepository;

    public function __construct(
        NewsletterRepository $newsletterRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
    }

    public function getPageData($pid): array
    {
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