<?php

namespace Mirko\Newsletter\Tests\Unit\Domain\Model;

use Mirko\Newsletter\Domain\Model\Link;
use Mirko\Newsletter\Domain\Model\Newsletter;

/**
 * Test case for class \Mirko\Newsletter\Domain\Model\Link.
 */
class LinkTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var Link
     */
    protected $subject = null;

    protected function setUp(): void
    {
        $this->subject = new Link();
    }

    protected function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function getUrlReturnsInitialValueForString()
    {
        $this->assertSame('', $this->subject->getUrl());
    }

    /**
     * @test
     */
    public function setUrlForStringSetsUrl()
    {
        $this->subject->setUrl('Conceived at T3CON10');
        $this->assertSame('Conceived at T3CON10', $this->subject->getUrl());
    }

    /**
     * @test
     */
    public function setNewsletterForNewsletterSetsNewsletter()
    {
        $newsletterFixture = new Newsletter();
        $this->subject->setNewsletter($newsletterFixture);

        $this->assertSame(
            $newsletterFixture,
            $this->subject->getNewsletter()
        );
    }

    /**
     * @test
     */
    public function getOpenedCountReturnsInitialValueForInteger()
    {
        $this->assertSame(
            0,
            $this->subject->getOpenedCount()
        );
    }

    /**
     * @test
     */
    public function setOpenedCountForIntegerSetsOpenedCount()
    {
        $this->subject->setOpenedCount(12);

        $this->assertSame(
            12,
            $this->subject->getOpenedCount()
        );
    }
}
