<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\CsvList.
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
     * @test
     */
    public function getTitleReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleForStringSetsTitle()
    {
        $this->subject->setTitle('Conceived at T3CON10');
        $this->assertClassHasAttribute('title', $this->subject, 'Conceived at T3CON10');
    }

    /**
     * @test
     */
    public function getPlainOnlyReturnsInitialValueForBoolean()
    {
        $this->assertFalse(
            $this->subject->getPlainOnly()
        );
        $this->assertFalse(
            $this->subject->isPlainOnly()
        );
    }

    /**
     * @test
     */
    public function setPlainOnlyForBooleanSetsPlainOnly()
    {
        $this->subject->setPlainOnly(true);

        $this->assertClassHasAttribute('plainOnly', $this->subject, true);
    }

    /**
     * @test
     */
    public function getLangReturnsInitialValueForString()
    {
        $this->assertSame(0, $this->subject->getLang());
    }

    /**
     * @test
     */
    public function setLangForStringSetsLang()
    {
        $this->subject->setLang(123);
        $this->assertClassHasAttribute('lang', $this->subject, 123);
    }

    /**
     * @test
     */
    public function getTypeReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getType());
    }

    /**
     * @test
     */
    public function setTypeForStringSetsType()
    {
        $this->subject->setType('Conceived at T3CON10');
        $this->assertClassHasAttribute('type', $this->subject, 'Conceived at T3CON10');
    }
}
