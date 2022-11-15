<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model;

use Mirko\Newsletter\Domain\Model\Email;
use Mirko\Newsletter\Domain\Model\Newsletter;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\Email.
 */
class EmailTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var Email
     */
    protected $subject = null;

    protected function setUp(): void
    {
        $this->subject = new Email();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
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
        $this->assertSame($dateTimeFixture, $this->subject->getBeginTime());
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
        $this->assertSame($dateTimeFixture, $this->subject->getEndTime());
    }

    /**
     * @test
     */
    public function getOpenTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getOpenTime()
        );
    }

    /**
     * @test
     */
    public function setOpenTimeForDateTimeSetsOpenTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setOpenTime($dateTimeFixture);
        $this->assertSame($dateTimeFixture, $this->subject->getOpenTime());
    }

    /**
     * @test
     */
    public function getBounceTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getBounceTime()
        );
    }

    /**
     * @test
     */
    public function setBounceTimeForDateTimeSetsBounceTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setBounceTime($dateTimeFixture);
        $this->assertSame($dateTimeFixture, $this->subject->getBounceTime());
    }

    /**
     * @test
     */
    public function getUnsubscribedReturnsInitialValueForBoolean()
    {
        $this->assertFalse(
            $this->subject->getUnsubscribed()
        );
    }

    /**
     * @test
     */
    public function setUnsubscribedForBooleanSetsUnsubscribed()
    {
        $this->subject->setUnsubscribed(true);
        $this->assertTrue($this->subject->getUnsubscribed());
    }

    /**
     * @test
     */
    public function getRecipientAddressReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getRecipientAddress());
    }

    /**
     * @test
     */
    public function setRecipientAddressForStringSetsRecipientAddress()
    {
        $this->subject->setRecipientAddress('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getRecipientAddress());
    }

    /**
     * @test
     */
    public function getRecipientDataReturnsInitialValueForArray()
    {
        $this->assertSame([], $this->subject->getRecipientData());
    }

    /**
     * @test
     */
    public function setRecipientDataForStringSetsRecipientData()
    {
        $this->subject->setRecipientData(['data1', 'data2']);
        $this->assertSame(['data1', 'data2'], $this->subject->getRecipientData());
    }

    /**
     * @test
     */
    public function getNewsletterReturnsInitialValueForNewsletter()
    {
        $this->assertNull(
            $this->subject->getNewsletter()
        );
    }

    /**
     * @test
     */
    public function setNewsletterForNewsletterSetsNewsletter()
    {
        $newsletterFixture = new Newsletter();
        $this->subject->setNewsletter($newsletterFixture);
        $this->assertSame($newsletterFixture, $this->subject->getNewsletter());
    }

    /**
     * @test
     */
    public function isOpened()
    {
        $this->assertFalse($this->subject->isOpened());
        $this->subject->setOpenTime(new \DateTime());
        $this->assertTrue($this->subject->isOpened());
    }

    /**
     * @test
     */
    public function isBounced()
    {
        $this->assertFalse($this->subject->isBounced());
        $this->subject->setBounceTime(new \DateTime());
        $this->assertTrue($this->subject->isBounced());
    }

    /**
     * @test
     */
    public function getAuthCode()
    {
        $email = $this->createMock(Email::class);
        $email->expects($this->any())->method('getUid')->will($this->returnValue(123));
        $email->setRecipientAddress('john@example.com');
        $this->assertSame('462aa2b1b9885a181e6d916a409d96c8', $email->getAuthCode());
    }
}
