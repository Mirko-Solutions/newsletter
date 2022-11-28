<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\PlainConverter;

use Mirko\Newsletter\Domain\Model\PlainConverter\Builtin;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\PlainConverter\Builtin.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\PlainConverter\Builtin
 */
class BuiltinTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var Builtin
     */
    protected $subject = null;

    protected function setUp(): void
    {
        $this->subject = new Builtin();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    public function testGetUrlReturnsInitialValueForString()
    {
        $html = file_get_contents(__DIR__ . '/input.html');
        $expected = file_get_contents(__DIR__ . '/builtin.txt');
        $actual = $this->subject->getPlainText($html, 'http://my-domain.com');
        $this->assertSame($expected, $actual);
    }
}
