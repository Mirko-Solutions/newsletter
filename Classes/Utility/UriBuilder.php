<?php

namespace Mirko\Newsletter\Utility;

use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\ExtensionService;

/**
 * Front end URI builder
 */
abstract class UriBuilder
{
    const EXTENSION_NAME = 'newsletter';
    const PLUGIN_NAME = 'p';
    const PAGE_TYPE = 1342671779;

    /**
     * Cache of URI to avoid hitting RealURL when possible
     *
     * @var array
     */
    private static $uriCache = [];

    /**
     * @var string plugin namespace for arguments
     */
    private static $namespace;

    /**
     * Return an array of namespaced arguments
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array $arguments
     *
     * @return array
     */
    private static function getNamespacedArguments($controllerName, $actionName, array $arguments)
    {
        $pluginNamespace = self::getNamespace();

        // Prepare arguments
        $arguments['action'] = $actionName;
        $arguments['controller'] = $controllerName;
        $namespacedArguments = [$pluginNamespace => $arguments];

        return $namespacedArguments;
    }

    /**
     * @return \TYPO3\CMS\Backend\Routing\UriBuilder
     */
    public static function getInstance(): \TYPO3\CMS\Backend\Routing\UriBuilder
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Routing\UriBuilder::class);
    }

    /**
     * Returns an ugly frontend URI from TCA context
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array $arguments
     *
     * @return string absolute URI
     */
    public static function buildFrontendUriFromTca($controllerName, $actionName, array $arguments = [])
    {
        $namespacedArguments = self::getNamespacedArguments($controllerName, $actionName, $arguments);
        $namespacedArguments['type'] = self::PAGE_TYPE;
        $uri = '/?' . http_build_query($namespacedArguments);

        return $uri;
    }

    /**
     * Returns a frontend URI independently of current context (backend or frontend)
     *
     * @param int $currentPid
     * @param string $controllerName
     * @param string $actionName
     * @param array $arguments
     *
     * @return string absolute URI
     */
    public static function buildFrontendUri($currentPid, $controllerName, $actionName, array $arguments = [])
    {
        if (!$currentPid) {
            return '';
        }

        $argumentsToRestore = array_intersect_key($arguments, array_fill_keys(['c', 'l'], null));
        unset($arguments['c'], $arguments['l']);
        $cacheKey = serialize([$currentPid, $controllerName, $actionName, $arguments]);

        if (array_key_exists($cacheKey, self::$uriCache)) {
            $uri = self::$uriCache[$cacheKey];
        } else {
            $namespacedArguments = self::getNamespacedArguments($controllerName, $actionName, $arguments);

            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($currentPid);

            // E.g.: "https://example.org/slug-of-page/?foo=1"
            $uri = (string)$site->getRouter()->generateUri((string)$currentPid, $namespacedArguments);
            if (!Uri::isAbsolute($uri)) {
                $uri = Tools::getBaseUrl($currentPid) . $uri;
            }

            $uri = static::removeCHash($uri);
            self::$uriCache[$cacheKey] = $uri;
        }


        // Re-append linkAuthCode
        if ($argumentsToRestore) {
            $prefix = mb_strpos($uri, '?') === false ? '?' : '&';
            $uri .= $prefix . http_build_query([self::getNamespace() => $argumentsToRestore]);
        }

        // If the `typeNum` is part of the arguments array, append the `type` parameter to the URI
        if (isset($arguments['type']) && $arguments['type'] === self::PAGE_TYPE) {
            $prefix = mb_strpos($uri, '?') === false ? '?' : '&';
            $uri .= $prefix . 'type=' . self::PAGE_TYPE;
        }

        return $uri;
    }

    /**
     * @param $uri
     * @return string
     */
    private static function removeCHash($uri): string
    {
        $parsedUrl = parse_url($uri);

        $query = $parsedUrl['query'];

        parse_str($query, $params);

        unset($params['cHash']);
        $params = http_build_query($params);

        return "{$parsedUrl['scheme']}://{$parsedUrl['host']}:{$parsedUrl['port']}{$parsedUrl['path']}?{$params}";
    }

    /**
     * Returns the plugin namespace for arguments
     *
     * @return string
     */
    private static function getNamespace()
    {
        if (!self::$namespace) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $extensionService = $objectManager->get(ExtensionService::class);
            self::$namespace = $extensionService->getPluginNamespace(self::EXTENSION_NAME, self::PLUGIN_NAME);
        }

        return self::$namespace;
    }
}
