<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList\Sql;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\Sql.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\RecipientList\Sql
 */
class SqlTest extends AbstractRecipientList
{
    protected function setUp(): void
    {
        $this->subject = new Sql();
    }


    public function testGetSqlStatementReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getSqlStatement());
    }


    public function testSetSqlStatementForStringSetsSqlStatement()
    {
        $this->subject->setSqlStatement('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getSqlStatement());
    }


    public function testGetSqlRegisterBounceReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getSqlRegisterBounce());
    }


    public function testSetSqlRegisterBounceForStringSetsSqlRegisterBounce()
    {
        $this->subject->setSqlRegisterBounce('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getSqlRegisterBounce());
    }


    public function testGetSqlRegisterClickReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getSqlRegisterClick());
    }


    public function testSetSqlRegisterClickForStringSetsSqlRegisterClick()
    {
        $this->subject->setSqlRegisterClick('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getSqlRegisterClick());
    }
}
