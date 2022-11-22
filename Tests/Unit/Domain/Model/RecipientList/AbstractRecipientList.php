<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\CsvList.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\RecipientList\CsvList
 */
abstract class AbstractRecipientList extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var object $subject
     */
    protected $subject = null;

    protected function tearDown():void
    {
        unset($this->subject);
    }

    /**
     * @covers
     */
    public function testGetTitleReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getTitle());
    }

    /**
     * @covers
     */
    public function testSetTitleForStringSetsTitle()
    {
        $this->subject->setTitle('Conceived at T3CON10');
        $this->assertClassHasAttribute('title', $this->subject::class, 'Conceived at T3CON10');
    }

    /**
     * @covers
     */
    public function testGetPlainOnlyReturnsInitialValueForBoolean()
    {
        $this->assertFalse(
            $this->subject->getPlainOnly()
        );
        $this->assertFalse(
            $this->subject->isPlainOnly()
        );
    }

    /**
     * @covers
     */
    public function testSetPlainOnlyForBooleanSetsPlainOnly()
    {
        $this->subject->setPlainOnly(true);

        $this->assertClassHasAttribute('plainOnly', $this->subject::class, true);
    }

    /**
     * @covers
     */
    public function testGetLangReturnsInitialValueForString()
    {
        $this->assertSame(0, $this->subject->getLang());
    }

    /**
     * @covers
     */
    public function testSetLangForStringSetsLang()
    {
        $this->subject->setLang(123);
        $this->assertClassHasAttribute('lang', $this->subject::class, 123);
    }

    /**
     * @covers
     */
    public function testGetTypeReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getType());
    }

    /**
     * @covers
     */
    public function testSetTypeForStringSetsType()
    {
        $this->subject->setType('Conceived at T3CON10');
        $this->assertClassHasAttribute('type', $this->subject::class, 'Conceived at T3CON10');
    }
}
