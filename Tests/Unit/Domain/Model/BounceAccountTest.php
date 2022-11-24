<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model;

use Mirko\Newsletter\Domain\Model\BounceAccount;
use Mirko\Newsletter\Tools;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\BounceAccount.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\BounceAccount
 */
class BounceAccountTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var BounceAccount
     */
    protected $subject = null;

    protected function setUp(): void
    {
        $this->subject = new BounceAccount();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    public function testGetEmailReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getEmail());
    }

    public function testSetEmailForStringSetsEmail()
    {
        $this->subject->setEmail('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getEmail());
    }

    public function testGetServerReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getServer());
    }

    public function testSetServerForStringSetsServer()
    {
        $this->subject->setServer('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getServer());
    }

    public function testGetProtocolReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getProtocol());
    }

    public function testSetProtocolForStringSetsProtocol()
    {
        $this->subject->setProtocol('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getProtocol());
    }

    public function testGetPortReturnsInitialValueForString()
    {
        $this->assertSame(0, $this->subject->getPort());
    }

    public function testSetPortForIntSetsPort()
    {
        $this->subject->setPort(25);
        $this->assertSame(25, $this->subject->getPort());
    }

    public function testGetUsernameReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getUsername());
    }

    public function testSetUsernameForStringSetsUsername()
    {
        $this->subject->setUsername('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getUsername());
    }

    public function testGetPasswordReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getPassword());
    }

    public function testSetPasswordForStringSetsPassword()
    {
        $this->subject->setPassword('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getPassword());
    }

    public function testGetConfigReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getConfig());
    }

    public function testSetConfigForStringSetsConfig()
    {
        $this->subject->setConfig('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getConfig());
    }


    public function testGetSubstitutedConfigDefault()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'encryptionKeyValue';
        $this->subject->setServer('mail.example.com');
        $this->subject->setProtocol('smtp');
        $this->subject->setPort(25);
        $this->subject->setUsername('john');
        $this->subject->setPassword(Tools::encrypt('hunter2'));
        $expected = 'poll mail.example.com proto smtp username "john" password "hunter2"';
        $this->assertSame($expected, $this->subject->getSubstitutedConfig());
    }


    public function testGetSubstitutedConfigCustom()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = 'encryptionKeyValue';
        $this->subject->setServer('pop.example.com');
        $this->subject->setProtocol('pop');
        $this->subject->setPort(123);
        $this->subject->setUsername('connor');
        $this->subject->setPassword(Tools::encrypt('skynet'));

        $config = 'server  : ###SERVER###
protocol: ###PROTOCOL###
port    : ###PORT###
username: ###USERNAME###
password: ###PASSWORD###';
        $this->subject->setConfig(Tools::encrypt($config));

        $expected = 'server  : pop.example.com
protocol: pop
port    : 123
username: connor
password: skynet';
        $this->assertSame($expected, $this->subject->getSubstitutedConfig());
    }
}
