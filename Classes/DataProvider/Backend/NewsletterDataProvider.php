<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;

class NewsletterDataProvider
{
    private NewsletterRepository $newsletterRepository;

    public function __construct(
        NewsletterRepository $newsletterRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
    }

    public function getDataByMode($mode, $pid) : array
    {
        $method = 'get'. ucfirst($mode).'PageData';
        return $this->{$method}($pid);
    }

    private function getStatusPageData($pid): array
    {
        /**
         * @var Newsletter $newsletters
         */
        $newsletters = $this->newsletterRepository->getLatest($pid);

        return [
            'status' => $newsletters->getStatus(),
            'validationResult' => $newsletters->getValidatedContent()
        ];
    }
}