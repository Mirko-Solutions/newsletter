<?php

namespace Mirko\Newsletter\Tests\Unit;

use Mirko\Newsletter\Tools;

/**
 * Unit test for \Mirko\Newsletter\Tools
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Tools
 */
class ToolsTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    public function testEncryption()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'encryptionKeyValue';
        $encrypted = Tools::encrypt('my value');
        $this->assertNotSame('my value', $encrypted, 'must be encrypted');
        $decrypted = Tools::decrypt($encrypted);
        $this->assertSame('my value', $decrypted, 'must be original value');
    }

    public function testUserAgent()
    {
        $GLOBALS['TYPO3_CONF_VARS']['HTTP']['headers']['User-Agent'] = 'User-Agent: TYPO3';
        $userAgent = Tools::getUserAgent();
        $this->assertSame(1, preg_match('~^User-Agent: TYPO3 Newsletter \(https://github.com/Mirko/newsletter\)$~', $userAgent));
    }
}
