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
     * Here the type is intentionally wrong because for some reasons it does not
     * work in TYPO3 7.3 and older if we specify the correct type of Newsletter
     *
     * @Extbase\ORM\Lazy
     * @var int
     */
    protected $newsletter;

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
    public function getNewsletter()
    {
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);

        return $newsletterRepository->findByUid($this->newsletter);
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
        $emailCount = $emailRepository->getCount($this->newsletter);

        if ($emailCount == 0) {
            return 0;
        }

        return round($this->getOpenedCount() * 100 / $emailCount, 2);
    }
}
