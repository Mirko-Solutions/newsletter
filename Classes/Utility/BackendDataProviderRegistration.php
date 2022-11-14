<?php

namespace Mirko\Newsletter\Utility;

use Mirko\Newsletter\DataProvider\Backend\BackendDataProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class BackendDataProviderRegistration
{
    protected static array $backendDataProviders = [];

    /**
     * Registers a backendDataProvider.
     *
     * @param string $moduleName
     * @param string $pageName
     * @param string $dataProviderClassName
     */
    public static function registerBackendDataProvider(
        string $moduleName,
        string $pageName,
        string $dataProviderClassName
    ): void {

        self::$backendDataProviders[$pageName] = [
            'moduleName' => $moduleName,
            'dataProviderClassName' => $dataProviderClassName
        ];
    }

    /**
     * Returns all currently registered BackendDataProviders.
     *
     * @return array An array of DataProviders instances
     */
    public static function getBackendDataProvidersInstanceByModule(): array
    {
        $backendDataProviders = [];

        foreach (self::$backendDataProviders as $componentName => $componentInfo) {
            $backendDataProviders[$componentInfo['moduleName']][$componentName] = self::getBackendDataProviderInstance(
                $componentName
            );
        }

        return $backendDataProviders;
    }

    /**
     * Returns all currently registered BackendDataProviders without instance.
     *
     * @return array
     */
    public static function getBackendDataProvidersByModule(): array
    {
        $backendDataProviders = [];

        foreach (self::$backendDataProviders as $componentName => $componentInfo) {
            $label = LocalizationUtility::translate($componentName, 'newsletter');
            $backendDataProviders[$componentInfo['moduleName']][$componentName]['name'] = $componentName;
            $backendDataProviders[$componentInfo['moduleName']][$componentName]['label'] = $label ?? ucfirst(
                $componentName
            );
        }

        return $backendDataProviders;
    }

    /**
     * Instantiates a data provider
     **/
    public static function getBackendDataProviderInstance(string $componentName): BackendDataProvider
    {
        if (!array_key_exists($componentName, self::$backendDataProviders)) {
            throw new \InvalidArgumentException(
                'No search component registered named ' . $componentName,
                1343398440
            );
        }

        $backendDataProvider = GeneralUtility::makeInstance(
            self::$backendDataProviders[$componentName]['dataProviderClassName']
        );

        if (!($backendDataProvider instanceof BackendDataProvider)) {
            throw new \RuntimeException(
                'Class ' . self::$backendDataProviders[$componentName]['dataProviderClassName'] . ' must implement interface ' . BackendDataProvider::class,
                1343398621
            );
        }

        return $backendDataProvider;
    }
}