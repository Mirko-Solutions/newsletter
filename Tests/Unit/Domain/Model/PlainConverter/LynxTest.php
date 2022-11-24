<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\PlainConverter;

use Mirko\Newsletter\Domain\Model\PlainConverter\Lynx;
use Mirko\Newsletter\Tests\Unit\AbstractUnitTestCase;
use Mirko\Newsletter\Tools;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\PlainConverter\Lynx.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\PlainConverter\Lynx
 */
class LynxTest extends AbstractUnitTestCase
{
    /**
     * @var Lynx
     */
    protected $subject = null;

    protected function setUp(): void
    {
        $this->subject = new Lynx();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    private function canRunLynx()
    {
        $this->loadConfiguration();

        $cmd = escapeshellcmd(Tools::confParam('path_to_lynx')) . ' --help';
        exec($cmd, $output, $statusCode);

        return $statusCode == 0;
    }

    public function testGetUrlReturnsInitialValueForString()
    {
        if (!$this->canRunLynx()) {
            $this->markTestSkipped('The command "' . Tools::confParam('path_to_lynx') . '" is not available.');
        }

        $html = file_get_contents(__DIR__ . '/input.html');
        $expected = file_get_contents(__DIR__ . '/lynx.txt');
        $actual = $this->subject->getPlainText($html, 'http://my-domain.com');
        $this->assertSame($expected, $actual);
    }
}
