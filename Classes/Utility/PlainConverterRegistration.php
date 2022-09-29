<?php

namespace Mirko\Newsletter\Utility;

use Mirko\Newsletter\Domain\Model\IPlainConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class PlainConverterRegistration
{
    protected static array $plainConverters = [];

    /**
     * Registers a PlainConverter.
     *
     * @param string $name
     * @param string $dataProviderClassName
     */
    public static function registerPlainConverter(
        string $name,
        string $dataProviderClassName
    ): void {

        self::$plainConverters[$name] = $dataProviderClassName;
    }

    /**
     * Returns all currently registered PlainConverter.
     *
     * @return array An array of PlainConverters instances
     */
    public static function getPlainConvertersInstances(): array
    {
        $plainConverters = [];

        foreach (self::$plainConverters as $componentName => $componentInfo) {
            $plainConverters[$componentName] = self::getPlainConverterInstance(
                $componentName
            );
        }

        return $plainConverters;
    }

    /**
     * Returns all currently registered PlainConverter without instance.
     *
     * @return array
     */
    public static function getPlainConvertersList(): array
    {
        $plainConverters = [];

        foreach (self::$plainConverters as $componentName => $componentInfo) {
            $label = LocalizationUtility::translate("plain_converter_{$componentName}", 'newsletter');
            $plainConverters[$componentName]['name'] = $componentName;
            $plainConverters[$componentName]['label'] = $label ?? ucfirst(
                $componentName
            );
            $plainConverters[$componentName]['className'] = $componentInfo;
        }

        return $plainConverters;
    }

    /**
     * Instantiates a plainConverter
     **/
    public static function getPlainConverterInstance(string $componentName): IPlainConverter
    {
        if (!array_key_exists($componentName, self::$plainConverters)) {
            throw new \InvalidArgumentException(
                'No search component registered named ' . $componentName,
                1343398440
            );
        }

        $backendDataProvider = GeneralUtility::makeInstance(
            self::$plainConverters[$componentName]
        );

        if (!($backendDataProvider instanceof IPlainConverter)) {
            throw new \RuntimeException(
                'Class ' . self::$plainConverters[$componentName] . ' must implement interface ' . IPlainConverter::class,
                1343398621
            );
        }

        return $backendDataProvider;
    }
}