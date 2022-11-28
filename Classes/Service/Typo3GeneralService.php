<?php

namespace Mirko\Newsletter\Service;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;

class Typo3GeneralService
{
    /**
     * @return BackendUserAuthentication
     */
    public static function getBackendUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    public static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return array
     */
    public static function getExtensionConfiguration(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['newsletter'] ?? [];
    }

    /**
     * @param array $configuration
     * @return void
     */
    public static function overrideExtensionConfiguration(array $configuration): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['newsletter'] = $configuration;
    }
}