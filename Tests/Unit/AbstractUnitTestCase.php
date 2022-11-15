<?php

namespace Mirko\Newsletter\Tests\Unit;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AbstractUnitTestCase extends UnitTestCase
{
    protected function inicializeEnviroment()
    {
        Environment::initialize(
            new ApplicationContext('Testing'),
            true,
            true,
            '',
            '',
            '',
            '',
            '/index.php',
            Environment::isWindows() ? 'WINDOWS' : 'UNIX'
        );
    }

    protected function loadConfiguration()
    {
        $manager = new ConfigurationManager();
        $path = $manager->getLocalConfigurationFileLocation();

        if (is_readable($path)) {
            $allConfig = $manager->getLocalConfiguration();
            $config = $allConfig['EXTENSIONS']['newsletter'];
        }

        if (!isset($config)) {
            $config = serialize([
                'path_to_lynx' => '/usr/bin/lynx',
                'replyto_name' => 'John Connor',
                'replyto_email' => 'john.connor@example.com',
            ]);
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['newsletter'] = $config;
    }
}
