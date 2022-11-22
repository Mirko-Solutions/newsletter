<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model;

use Mirko\Newsletter\Domain\Model\Email;
use Mirko\Newsletter\Domain\Model\Newsletter;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\Email.
 */
/**
 * @coversDefaultClass \Mirko\Newsletter\Domain\Model\Email
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
     * @covers
     */
    public function testGetBeginTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getBeginTime()
        );
    }

    /**
     * @covers
     */
    public function testSetBeginTimeForDateTimeSetsBeginTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setBeginTime($dateTimeFixture);
        $this->assertSame($dateTimeFixture, $this->subject->getBeginTime());
    }

    /**
     * @covers
     */
    public function testGetEndTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getEndTime()
        );
    }

    /**
     * @covers
     */
    public function testSetEndTimeForDateTimeSetsEndTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setEndTime($dateTimeFixture);
        $this->assertSame($dateTimeFixture, $this->subject->getEndTime());
    }

    /**
     * @covers
     */
    public function testGetOpenTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getOpenTime()
        );
    }

    /**
     * @covers
     */
    public function testSetOpenTimeForDateTimeSetsOpenTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setOpenTime($dateTimeFixture);
        $this->assertSame($dateTimeFixture, $this->subject->getOpenTime());
    }

    /**
     * @covers
     */
    public function testGetBounceTimeReturnsInitialValueForDateTime()
    {
        $this->assertNull(
            $this->subject->getBounceTime()
        );
    }

    /**
     * @covers
     */
    public function testSetBounceTimeForDateTimeSetsBounceTime()
    {
        $dateTimeFixture = new \DateTime();
        $this->subject->setBounceTime($dateTimeFixture);
        $this->assertSame($dateTimeFixture, $this->subject->getBounceTime());
    }

    /**
     * @covers
     */
    public function testGetUnsubscribedReturnsInitialValueForBoolean()
    {
        $this->assertFalse(
            $this->subject->getUnsubscribed()
        );
    }

    /**
     * @covers
     */
    public function testSetUnsubscribedForBooleanSetsUnsubscribed()
    {
        $this->subject->setUnsubscribed(true);
        $this->assertTrue($this->subject->getUnsubscribed());
    }

    /**
     * @covers
     */
    public function testGetRecipientAddressReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getRecipientAddress());
    }

    /**
     * @covers
     */
    public function testSetRecipientAddressForStringSetsRecipientAddress()
    {
        $this->subject->setRecipientAddress('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getRecipientAddress());
    }

    /**
     * @covers
     */
    public function testGetRecipientDataReturnsInitialValueForArray()
    {
        $this->assertSame([], $this->subject->getRecipientData());
    }

    /**
     * @covers
     */
    public function testSetRecipientDataForStringSetsRecipientData()
    {
        $this->subject->setRecipientData(['data1', 'data2']);
        $this->assertSame(['data1', 'data2'], $this->subject->getRecipientData());
    }

    /**
     * @covers
     */
    public function testGetNewsletterReturnsInitialValueForNewsletter()
    {
        $this->assertNull(
            $this->subject->getNewsletter()
        );
    }

    /**
     * @covers
     */
    public function testSetNewsletterForNewsletterSetsNewsletter()
    {
        $newsletterFixture = new Newsletter();
        $this->subject->setNewsletter($newsletterFixture);
        $this->assertSame($newsletterFixture, $this->subject->getNewsletter());
    }

    /**
     * @covers
     */
    public function testIsOpened()
    {
        $this->assertFalse($this->subject->isOpened());
        $this->subject->setOpenTime(new \DateTime());
        $this->assertTrue($this->subject->isOpened());
    }

    /**
     * @covers
     */
    public function testIsBounced()
    {
        $this->assertFalse($this->subject->isBounced());
        $this->subject->setBounceTime(new \DateTime());
        $this->assertTrue($this->subject->isBounced());
    }

    /**
     * @covers
     */
    public function testGetAuthCode()
    {
        $email = new Email();
        $email->setUid(123);
        $email->setRecipientAddress('john@example.com');
        $this->assertSame('462aa2b1b9885a181e6d916a409d96c8', $email->getAuthCode());
    }
}
