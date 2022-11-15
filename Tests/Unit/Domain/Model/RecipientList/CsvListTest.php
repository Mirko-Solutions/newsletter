<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList\CsvList;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\RecipientList\CsvList.
 */
class CsvListTest extends CsvFileTest
{
    protected function setUp():void
    {
        $this->subject = new CsvList();
    }

    /**
     * @test
     */
    public function getCsvSeparatorReturnsInitialValueForString()
    {
        $this->assertSame(',', $this->subject->getCsvSeparator());
    }

    /**
     * @test
     */
    public function getCsvValuesReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getCsvValues());
    }

    /**
     * @test
     */
    public function setCsvValuesForStringSetsCsvValues()
    {
        $this->subject->setCsvValues('Conceived at T3CON10');
        $this->assertClassHasAttribute('csvValues', $this->subject);
        $this->assertSame('Conceived at T3CON10', $this->subject->getCsvValues());
    }

    protected function prepareDataForEnumeration()
    {
        $values = file_get_contents(__DIR__ . '/data.csv');

        $this->subject->setCsvValues($values);
    }
}
