<?php

namespace Mirko\Newsletter\Service;

use Mirko\Newsletter\Utility\BackendDataProviderRegistration;

class NewsletterModuleService
{
    public function getDataForPage($page, $pid) : array
    {
        $dataProvider = BackendDataProviderRegistration::getBackendDataProviderInstance($page);

        return $dataProvider->getPageData($pid);
    }
}