<?php

namespace Mirko\Newsletter\Domain\Model;

use Mirko\Newsletter\Domain\Repository\EmailRepository;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * Link
 */
class Link extends AbstractEntity
{
    /**
     * url
     *
     * @var string
     */
    protected $url = '';

    /**
     * newsletter
     */
    protected Newsletter $newsletter;

    /**
     * opened count
     *
     * @var int
     */
    protected $openedCount = 0;

    /**
     * Setter for url
     *
     * @param string $url url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Getter for url
     *
     * @return string url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for newsletter
     *
     * @param Newsletter $newsletter newsletter
     */
    public function setNewsletter(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * Getter for newsletter
     *
     * @return Newsletter newsletter
     */
    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    /**
     * Setter for openedCount
     *
     * @param int $openedCount openedCount
     */
    public function setOpenedCount($openedCount)
    {
        $this->openedCount = $openedCount;
    }

    /**
     * Getter for openedCount
     *
     * @return int openedCount
     */
    public function getOpenedCount()
    {
        return $this->openedCount;
    }

    public function getOpenedPercentage()
    {
        $emailRepository = GeneralUtility::makeInstance(EmailRepository::class);
        $emailCount = $emailRepository->getCount($this->newsletter->getUid());

        if ($emailCount === 0) {
            return 0;
        }

        return round($this->getOpenedCount() * 100 / $emailCount, 2);
    }
}
