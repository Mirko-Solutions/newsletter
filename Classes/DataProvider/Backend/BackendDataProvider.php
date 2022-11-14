<?php

namespace Mirko\Newsletter\DataProvider\Backend;

use Mirko\Newsletter\Domain\Model\Newsletter;

interface BackendDataProvider
{
    public function getPageData($pid): array;
}