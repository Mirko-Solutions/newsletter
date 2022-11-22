<?php

namespace Mirko\Newsletter\Tests\Unit;

use Mirko\Newsletter\Service\Typo3GeneralService;
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
            $config = [
                'append_url' => '',
                'attach_images' => '1',
                'fetch_path' => 'http://example.com',
                'keep_messages' => '0',
                'mails_per_round' => '100',
                'notification_email' => 'user',
                'path_to_fetchmail' => '/usr/bin/fetchmail',
                'path_to_lynx' => '/usr/bin/lynx',
                'replyto_name' => 'John Connor',
                'replyto_email' => 'john.connor@example.com',
                'sender_email' => 'user@gmail.com',
                'sender_name' => 'user',
                'unsubscribe_redirect' => '',
            ];
        }

        Typo3GeneralService::overrideExtensionConfiguration($config);
    }
}
