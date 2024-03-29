<?php

namespace Mirko\Newsletter\Domain\Model;

use DateTime;
use Mirko\Newsletter\Utility\UriBuilder;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use UnexpectedValueException;
use function bin2hex;
use function random_bytes;
use function strlen;

/**
 * Email
 */
class Email extends AbstractEntity implements EmailInterface
{
    private const INITIAL_AUTH_CODE_PREFIX = 'initial-';

    /**
     * beginTime
     *
     * @var DateTime
     */
    protected $beginTime;

    /**
     * endTime
     *
     * @var DateTime
     */
    protected $endTime;

    /**
     * recipientAddress
     *
     * @var string
     * @Extbase\Validate(validator="NotEmpty")
     */
    protected $recipientAddress = '';

    /**
     * recipientData
     *
     * @var string
     */
    protected $recipientData = 'a:0:{}';

    /**
     * openeTime
     *
     * @var DateTime
     */
    protected $openTime;

    /**
     * bounceTime
     *
     * @var DateTime
     */
    protected $bounceTime;

    /**
     * newsletter
     *
     * @Extbase\ORM\Lazy
     * @var Newsletter
     */
    protected $newsletter;

    /**
     * Whether the recipient of this email requested to unsubscribe.
     *
     * @var bool
     * @Extbase\Validate(validator="NotEmpty")
     */
    protected $unsubscribed = false;

    /**
     * authCode
     *
     * The MD5 hash used to identify an email in user content
     * (So we don't need to expose ID in newsletter content)
     *
     * @var string
     */
    protected $authCode = '';

    public function __construct()
    {
        $this->authCode = self::INITIAL_AUTH_CODE_PREFIX . bin2hex(random_bytes(20));
    }

    /**
     * @param int $uid
     * @return void
     */
    public function setUid(int $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * Setter for beginTime
     *
     * @param DateTime $beginTime beginTime
     */
    public function setBeginTime(DateTime $beginTime)
    {
        $this->beginTime = $beginTime;
    }

    /**
     * Getter for beginTime
     *
     * @return DateTime beginTime
     */
    public function getBeginTime()
    {
        return $this->beginTime;
    }

    /**
     * Setter for endTime
     *
     * @param DateTime $endTime endTime
     */
    public function setEndTime(DateTime $endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Getter for endTime
     *
     * @return DateTime endTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Setter for recipientAddress
     *
     * @param string $recipientAddress recipientAddress
     */
    public function setRecipientAddress($recipientAddress)
    {
        $this->recipientAddress = $recipientAddress;
        $this->computeAuthCode();
    }

    /**
     * Get the recipient address (eg: john@example.com)
     *
     * @return string recipientAddress
     */
    public function getRecipientAddress()
    {
        return $this->recipientAddress;
    }

    /**
     * Setter for recipientData
     *
     * @param array $recipientData recipientData
     */
    public function setRecipientData(array $recipientData)
    {
        $this->recipientData = serialize($recipientData);
    }

    /**
     * Get recipient data.
     *
     * This return an array of all custom data for this recipient. That
     * typically includes all fields selected in a SQL RecipientList.
     *
     * @return string[] recipientData
     */
    public function getRecipientData()
    {
        return unserialize($this->recipientData);
    }

    /**
     * Compute authCode
     */
    private function computeAuthCode()
    {
        if ($this->getUid()) {
            $this->authCode = md5($this->getUid() . $this->getRecipientAddress());
        } else {
            throw new UnexpectedValueException('Can not compute the auth code because the UID is not set');
        }
    }

    /**
     * Getter for authCode
     *
     * This is set on DB insertion and can never be changed
     *
     * @return string authCode
     */
    public function getAuthCode()
    {
        if (substr($this->authCode, 0, strlen(self::INITIAL_AUTH_CODE_PREFIX)) === self::INITIAL_AUTH_CODE_PREFIX) {
            throw new UnexpectedValueException('Auth code has not been computed');
        }

        return $this->authCode;
    }

    /**
     * Setter for openTime
     *
     * @param DateTime $openTime openTime
     */
    public function setOpenTime(DateTime $openTime)
    {
        $this->openTime = $openTime;
    }

    /**
     * Getter for openTime
     *
     * @return DateTime openTime
     */
    public function getOpenTime()
    {
        return $this->openTime;
    }

    /**
     * Returns the state of opened
     *
     * @return bool the state of opened
     */
    public function isOpened()
    {
        return (bool)$this->getOpenTime();
    }

    /**
     * Setter for bounceTime
     *
     * @param DateTime $bounceTime bounceTime
     */
    public function setBounceTime(DateTime $bounceTime)
    {
        $this->bounceTime = $bounceTime;
    }

    /**
     * Getter for bounceTime
     *
     * @return DateTime bounceTime
     */
    public function getBounceTime()
    {
        return $this->bounceTime;
    }

    /**
     * Returns the state of bounced
     *
     * @return bool the state of bounced
     */
    public function isBounced()
    {
        return (bool)$this->getBounceTime();
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
        return $this->newsletter;
    }

    /**
     * Setter for unsubscribed
     *
     * @param bool $unsubscribed whether the recipient of this email requested to unsubscribe
     */
    public function setUnsubscribed($unsubscribed)
    {
        $this->unsubscribed = $unsubscribed;
    }

    /**
     * Getter for unsubscribed
     *
     * @return bool whether the recipient of this email requested to unsubscribe
     */
    public function getUnsubscribed()
    {
        return $this->unsubscribed;
    }

    /**
     * Return the URL to view the newsletter
     *
     * @return string
     */
    public function getViewUrl()
    {
        return UriBuilder::buildFrontendUri($this->getPid(), 'Email', 'show', $this->getUriArguments());
    }

    /**
     * Return the URL to unsubscribe from the newsletter
     *
     * @return string
     */
    public function getUnsubscribeUrl()
    {
        return UriBuilder::buildFrontendUri($this->getPid(), 'Email', 'unsubscribe', $this->getUriArguments());
    }

    /**
     * Return the URL to register when an email was opened
     *
     * @return string
     */
    public function getOpenedUrl()
    {
        return UriBuilder::buildFrontendUri($this->getPid(), 'Email', 'opened', $this->getUriArguments());
    }

    /**
     * Get arguments for URI
     *
     * @return array
     */
    private function getUriArguments()
    {
        $args = ['c' => $this->getAuthCode(), 'type' => 1342671779];

        $recipientData = $this->getRecipientData();
        $language = array_key_exists('L', $recipientData) ? $recipientData['L'] : 0;
        if ($language) {
            $args['L'] = $language;
        }

        return $args;
    }
}
