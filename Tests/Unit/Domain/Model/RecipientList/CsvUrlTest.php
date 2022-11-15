<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList\CsvUrl;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\RecipientList\CsvUrl.
 */
class CsvUrlTest extends CsvFileTest
{
    protected function setUp() :void
    {
        $this->subject = new CsvUrl();
    }

    /**
     * @test
     */
    public function getCsvUrlReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getCsvUrl());
    }

    /**
     * @test
     */
    public function setCsvUrlForStringSetsCsvUrl()
    {
        $this->subject->setCsvUrl('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getCsvUrl());
    }

    protected function prepareDataForEnumeration()
    {
        $this->subject->setCsvUrl(__DIR__ . '/data.csv');
    }
}
