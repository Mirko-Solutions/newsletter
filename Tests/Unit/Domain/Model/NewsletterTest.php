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

    /**
     * @test
     */
    public function setUid()
    {
        $this->assertNull($this->subject->getUid());
        $this->subject->setUid(123);
        $this->assertSame(123, $this->subject->getUid());
    }

    /**
     * @test
     */
    public function getPlannedTimeReturnsInitialValueForDateTime()
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $plannedTime = $this->subject->getPlannedTime();
        $this->assertNotNull($plannedTime);

        $plannedTime->setTime(0, 0, 0);
        $this->assertSame($today->format(\DateTime::ATOM), $plannedTime->format(\DateTime::ATOM));
    }

    /**
     * @test
     */
    public function setPlannedTimeForDateTimeSetsPlannedTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setPlannedTime($dateTimeFixture);

        $this->assertSame($dateTimeFixture, $this->subject->getPlannedTime());
    }

    /**
     * @test
     */
    public function getBeginTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getBeginTime()
        );
    }

    /**
     * @test
     */
    public function setBeginTimeForDateTimeSetsBeginTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setBeginTime($dateTimeFixture);

        $this->assertSame(
            $dateTimeFixture,
            $this->subject->getBeginTime()
        );
    }

    /**
     * @test
     */
    public function getEndTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getEndTime()
        );
    }

    /**
     * @test
     */
    public function setEndTimeForDateTimeSetsEndTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setEndTime($dateTimeFixture);

        $this->assertSame(
            $dateTimeFixture,
            $this->subject->getEndTime()
        );
    }

    /**
     * @test
     */
    public function getRepetitionReturnsInitialValueForInteger()
    {
        $this->assertSame(
            0,
            $this->subject->getRepetition()
        );
    }

    /**
     * @test
     */
    public function setRepetitionForIntegerSetsRepetition()
    {
        $this->subject->setRepetition(12);

        $this->assertSame(
            12,
            $this->subject->getRepetition()
        );
    }

    /**
     * @test
     */
    public function getPlainConverterReturnsInitialValueForString()
    {
        $converter = $this->subject->getPlainConverter();
        $this->assertSame(
            Builtin::class,
            $converter
        );

        $this->assertTrue(class_exists($converter));
    }

    /**
     * @test
     */
    public function setPlainConverterForStringSetsPlainConverter()
    {
        $this->subject->setPlainConverter('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->subject->getPlainConverter()
        );
    }

    /**
     * @test
     */
    public function getPlainConverterInstance()
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
    public function getPlainConverterInstanceThrowsException()
    {
        $this->expectException(\Exception::class);
        $this->subject->setPlainConverter('stdClass');
        $this->subject->getPlainConverterInstance();
    }

    /**
     * @test
     */
    public function getIsTestReturnsInitialValueForBoolean()
    {
        $this->assertFalse($this->subject->getIsTest());
        $this->assertFalse($this->subject->isIsTest());
    }

    /**
     * @test
     */
    public function setIsTestForBooleanSetsIsTest()
    {
        $this->subject->setIsTest(true);

        $this->assertTrue(
            $this->subject->isIsTest()
        );
    }

    /**
     * @test
     */
    public function getBounceAccountReturnsInitialValueForBounceAccount()
    {
        $this->assertNull(
            $this->subject->getBounceAccount()
        );
    }

    /**
     * @test
     */
    public function setBounceAccountForBounceAccountSetsBounceAccount()
    {
        $bounceAccountFixture = new BounceAccount();
        $this->subject->setBounceAccount($bounceAccountFixture);

        $this->assertSame(
            $bounceAccountFixture,
            $this->subject->getBounceAccount()
        );
    }

    /**
     * @test
     */
    public function getUidBounceAccount()
    {
        $this->assertNull($this->subject->getUidBounceAccount());

        $bounceAccount = $this->createMock(BounceAccount::class);
        $bounceAccount->expects($this->once())->method('getUid')->will($this->returnValue(123));
        $this->subject->setBounceAccount($bounceAccount);
        $this->assertSame(123, $this->subject->getUidBounceAccount());
    }

    /**
     * @test
     */
    public function setSenderNameForStringSetsSenderName()
    {
        $this->subject->setSenderName('Conceived at T3CON10');

        $this->assertSame(
            'Conceived at T3CON10',
            $this->subject->getSenderName()
        );

        $this->assertSame('Conceived at T3CON10', $this->subject->getSenderName());
    }

    /**
     * @test
     */
    public function setSenderEmailForStringSetsSenderEmail()
    {
        $this->subject->setSenderEmail('john@example.com');

        $this->assertSame('john@example.com', $this->subject->getSenderEmail());
    }

    /**
     * @test
     */
    public function getInjectOpenSpyReturnsInitialValueForBoolean()
    {
        $this->assertTrue($this->subject->getInjectOpenSpy());
        $this->assertTrue($this->subject->isInjectOpenSpy());
    }

    /**
     * @test
     */
    public function setInjectOpenSpyForBooleanSetssetInjectOpenSpy()
    {
        $this->subject->setInjectOpenSpy(true);

        $this->assertTrue(true, $this->subject->getInjectOpenSpy());
    }

    /**
     * @test
     */
    public function getInjectLinksSpyReturnsInitialValueForBoolean()
    {
        $this->assertTrue($this->subject->getInjectLinksSpy());
        $this->assertTrue($this->subject->isInjectLinksSpy());
    }

    /**
     * @test
     */
    public function setInjectLinksSpyForBooleanSetsInjectLinksSpy()
    {
        $this->subject->setInjectLinksSpy(false);

        $this->assertFalse($this->subject->getInjectLinksSpy());
    }

    /**
     * @test
     */
    public function getRecipientListReturnsInitialValueForRecipientList()
    {
        $this->assertNull(
            $this->subject->getRecipientList()
        );
    }

    /**
     * @test
     */
    public function setRecipientListForRecipientListSetsRecipientList()
    {
        $recipientListFixture = new BeUsers();
        $this->subject->setRecipientList($recipientListFixture);
        $this->assertSame($recipientListFixture, $this->subject->getRecipientList());
    }

    /**
     * @test
     */
    public function getUidRecipientList()
    {
        $this->assertNull($this->subject->getUidRecipientList());

        $recipientList = $this->createMock(BeUsers::class);
        $recipientList->expects($this->once())->method('getUid')->will($this->returnValue(123));
        $this->subject->setRecipientList($recipientList);
        $this->assertSame(123, $this->subject->getUidRecipientList());
    }

    /**
     * @test
     */
    public function getReplytoName()
    {
        $this->assertSame(
            'John Connor',
            $this->subject->getReplytoName(),
            'sould return globally configured default value'
        );
        $this->subject->setReplytoName('My custom name');
        $this->assertSame('My custom name', $this->subject->getReplytoName(), 'sould return locally set value');
    }

    /**
     * @test
     */
    public function getReplytoEmail()
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
