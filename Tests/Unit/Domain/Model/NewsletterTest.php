<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model;

use Mirko\Newsletter\Domain\Model\BounceAccount;
use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Model\PlainConverter\Builtin;
use Mirko\Newsletter\Domain\Model\PlainConverter\Lynx;
use Mirko\Newsletter\Domain\Model\RecipientList\BeUsers;
use Mirko\Newsletter\Tests\Unit\AbstractUnitTestCase;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\Newsletter.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\Newsletter
 */
class NewsletterTest extends AbstractUnitTestCase
{
    /**
     * @var Newsletter
     */
    protected $subject = null;

    protected function setUp(): void
    {
        $this->inicializeEnviroment();
        $this->loadConfiguration();
        $this->subject = new Newsletter();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }


    public function testSetUid()
    {
        $this->assertNull($this->subject->getUid());
        $this->subject->setUid(123);
        $this->assertSame(123, $this->subject->getUid());
    }


    public function testGetPlannedTimeReturnsInitialValueForDateTime()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $plannedTime = $this->subject->getPlannedTime();
        $this->assertNotNull($plannedTime);

        $plannedTime->setTime(0, 0, 0);
        $this->assertSame($today->format(\DateTime::ATOM), $plannedTime->format(\DateTime::ATOM));
    }


    public function testSetPlannedTimeForDateTimeSetsPlannedTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setPlannedTime($dateTimeFixture);

        $this->assertSame($dateTimeFixture, $this->subject->getPlannedTime());
    }


    public function testGetBeginTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getBeginTime()
        );
    }


    public function testSetBeginTimeForDateTimeSetsBeginTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setBeginTime($dateTimeFixture);

        $this->assertSame(
            $dateTimeFixture,
            $this->subject->getBeginTime()
        );
    }


    public function testGetEndTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getEndTime()
        );
    }


    public function testSetEndTimeForDateTimeSetsEndTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setEndTime($dateTimeFixture);

        $this->assertSame(
            $dateTimeFixture,
            $this->subject->getEndTime()
        );
    }


    public function testGetRepetitionReturnsInitialValueForInteger()
    {
        $this->assertSame(
            0,
            $this->subject->getRepetition()
        );
    }


    public function testSetRepetitionForIntegerSetsRepetition()
    {
        $this->subject->setRepetition(12);

        $this->assertSame(
            12,
            $this->subject->getRepetition()
        );
    }


    public function testGetPlainConverterReturnsInitialValueForString()
    {
        $converter = $this->subject->getPlainConverter();
        $this->assertSame(
            Builtin::class,
            $converter
        );

        $this->assertTrue(class_exists($converter));
    }


    public function testSetPlainConverterForStringSetsPlainConverter()
    {
        $this->subject->setPlainConverter('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->subject->getPlainConverter()
        );
    }


    public function testGetPlainConverterInstance()
    {
        $classes = [
            'NonExistingClassFooBar' => Builtin::class,
            Builtin::class => Builtin::class,
            Lynx::class => Lynx::class,
        ];

        foreach ($classes as $class => $expected) {
            $this->subject->setPlainConverter($class);
            $this->assertInstanceOf($expected, $this->subject->getPlainConverterInstance());
        }
    }

    /**
     * @test
     *
     */
    public function testGetPlainConverterInstanceThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->subject->setPlainConverter('stdClass');
        $this->subject->getPlainConverterInstance();
    }


    public function testGetIsTestReturnsInitialValueForBoolean()
    {
        $this->assertFalse($this->subject->getIsTest());
        $this->assertFalse($this->subject->isIsTest());
    }


    public function testSetIsTestForBooleanSetsIsTest()
    {
        $this->subject->setIsTest(true);

        $this->assertTrue(
            $this->subject->isIsTest()
        );
    }


    public function testGetBounceAccountReturnsInitialValueForBounceAccount()
    {
        $this->assertNull(
            $this->subject->getBounceAccount()
        );
    }


    public function testSetBounceAccountForBounceAccountSetsBounceAccount()
    {
        $bounceAccountFixture = new BounceAccount();
        $this->subject->setBounceAccount($bounceAccountFixture);

        $this->assertSame(
            $bounceAccountFixture,
            $this->subject->getBounceAccount()
        );
    }


    public function testGetUidBounceAccount()
    {
        $this->assertNull($this->subject->getUidBounceAccount());

        $bounceAccount = $this->createMock(BounceAccount::class);
        $bounceAccount->expects($this->once())->method('getUid')->will($this->returnValue(123));
        $this->subject->setBounceAccount($bounceAccount);
        $this->assertSame(123, $this->subject->getUidBounceAccount());
    }


    public function testGetInjectOpenSpyReturnsInitialValueForBoolean()
    {
        $this->assertTrue($this->subject->getInjectOpenSpy());
        $this->assertTrue($this->subject->isInjectOpenSpy());
    }


    public function testSetInjectOpenSpyForBooleanSetssetInjectOpenSpy()
    {
        $this->subject->setInjectOpenSpy(true);

        $this->assertTrue(true, $this->subject->getInjectOpenSpy());
    }


    public function testGetInjectLinksSpyReturnsInitialValueForBoolean()
    {
        $this->assertTrue($this->subject->getInjectLinksSpy());
        $this->assertTrue($this->subject->isInjectLinksSpy());
    }


    public function testSetInjectLinksSpyForBooleanSetsInjectLinksSpy()
    {
        $this->subject->setInjectLinksSpy(false);

        $this->assertFalse($this->subject->getInjectLinksSpy());
    }


    public function testGetRecipientListReturnsInitialValueForRecipientList()
    {
        $this->assertNull(
            $this->subject->getRecipientList()
        );
    }


    public function testSetRecipientListForRecipientListSetsRecipientList()
    {
        $recipientListFixture = new BeUsers();
        $this->subject->setRecipientList($recipientListFixture);
        $this->assertSame($recipientListFixture, $this->subject->getRecipientList());
    }


    public function testGetUidRecipientList()
    {
        $this->assertNull($this->subject->getUidRecipientList());

        $recipientList = $this->createMock(BeUsers::class);
        $recipientList->expects($this->once())->method('getUid')->will($this->returnValue(123));
        $this->subject->setRecipientList($recipientList);
        $this->assertSame(123, $this->subject->getUidRecipientList());
    }


    public function testGetReplytoName()
    {
        $this->assertSame(
            'John Connor',
            $this->subject->getReplytoName(),
            'sould return globally configured default value'
        );
        $this->subject->setReplytoName('My custom name');
        $this->assertSame('My custom name', $this->subject->getReplytoName(), 'sould return locally set value');
    }


    public function testGetReplytoEmail()
    {
        $this->assertSame(
            'john.connor@example.com',
            $this->subject->getReplytoEmail(),
            'sould return globally configured default value'
        );
        $this->subject->setReplytoEmail('custom@example.com');
        $this->assertSame('custom@example.com', $this->subject->getReplytoEmail(), 'sould return locally set value');
    }
}
