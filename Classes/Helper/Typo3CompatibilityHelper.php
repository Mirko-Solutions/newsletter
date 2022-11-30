<?php

namespace Mirko\Newsletter\Helper;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class Typo3CompatibilityHelper implements \TYPO3\CMS\Core\SingletonInterface
{
    public static function checkRequestType($request)
    {
        if (!$request instanceof ServerRequestInterface) {
            return static::getRequest();
        }

        return $request;
    }

    // use Psr\Http\Message\ServerRequestInterface;
    private static function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    public static function typo3VersionIs10() : bool
    {
        $version = new Typo3Version();

        return $version->getMajorVersion() === 10;
    }
}