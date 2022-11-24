<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList\CsvList;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\RecipientList\CsvList.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\RecipientList\CsvList
 */
class CsvListTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected function setUp():void
    {
        $this->subject = new CsvList();
    }


    public function testGetCsvSeparatorReturnsInitialValueForString()
    {
        $this->assertSame(',', $this->subject->getCsvSeparator());
    }


    public function testGetCsvValuesReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getCsvValues());
    }


    public function testSetCsvValuesForStringSetsCsvValues()
    {
        $this->subject->setCsvValues('Conceived at T3CON10');
        $this->assertClassHasAttribute('csvValues', $this->subject::class);
        $this->assertSame('Conceived at T3CON10', $this->subject->getCsvValues());
    }

    protected function prepareDataForEnumeration()
    {
        $values = file_get_contents(__DIR__ . '/data.csv');

        $this->subject->setCsvValues($values);
    }
}
