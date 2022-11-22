<?php

namespace Mirko\Newsletter\Domain\Model\PlainConverter;

use Mirko\Newsletter\Domain\Model\IPlainConverter;
use Mirko\Newsletter\ThirdParty\Html2Text;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Convert HTML to plain text using builtin Html2Text tool
 */
class Builtin extends Html2Text implements IPlainConverter
{
    public function getPlainText($content, $baseUrl)
    {
        $converter = new Html2Text(
            $content, [
                'do_links' => 'table',
            ]
        );
        $converter->setBaseUrl($baseUrl);

        return $converter->getText();
    }
}
