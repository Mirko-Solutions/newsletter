<?php

namespace Mirko\Newsletter\Domain\Model;

use DateTime;
use DateTimeInterface;
use Mirko\Newsletter\Service\NewsletterService;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Site\SiteFinder;
use Mirko\Newsletter\Utility\Validator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use Mirko\Newsletter\Domain\Repository\EmailRepository;
use Mirko\Newsletter\Domain\Model\PlainConverter\Builtin;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Domain\Repository\BounceAccountRepository;
use Mirko\Newsletter\Domain\Repository\RecipientListRepository;

/**
 * Newsletter represents a page to be sent to a specific time to several recipients.
 */
class Newsletter extends AbstractEntity
{
    /**
     * When the newsletter will start sending emails
     *
     * @var DateTime
     * @Extbase\Validate(validator="NotEmpty")
     */
    protected $plannedTime;

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
     * 0-7 values to indicates when this newsletter will repeat
     *
     * @var int
     */
    protected $repetition = 0;

    /**
     * Tool used to convert to plain text
     *
     * @var string
     */
    protected $plainConverter = Builtin::class;

    /**
     * Whether this newsletter is for test purpose. If it is it will be ignored in statistics
     *
     * @var bool
     * @Extbase\Validate(validator="NotEmpty")
     */
    protected $isTest = false;

    /**
     * List of files to be attached (comma separated list)
     *
     * @var string
     */
    protected string $attachments = '';

    /**
     * The name of the newsletter sender
     *
     * @var string
     * @Extbase\Validate(validator="NotEmpty")
     */
    protected $senderName;

    /**
     * The email of the newsletter sender
     *
     * @var string
     * @Extbase\Validate(validator="NotEmpty")
     */
    protected $senderEmail;

    /**
     * The Reply-To name of the newsletter
     *
     * @var string
     */
    protected $replytoName;

    /**
     * The Reply-To <email> of the newsletter
     *
     * @var string
     */
    protected $replytoEmail;

    /**
     * injectOpenSpy
     *
     * @var bool
     */
    protected $injectOpenSpy = true;

    /**
     * injectLinksSpy
     *
     * @var bool
     */
    protected $injectLinksSpy = true;

    /**
     * bounceAccount
     *
     * @Extbase\ORM\Lazy
     * @var BounceAccount
     */
    protected $bounceAccount;

    /**
     * UID of the bounce account. Only exist for ease of use with ExtJS
     *
     * @var int
     */
    protected $uidBounceAccount;

    /**
     * recipientList
     *
     * @Extbase\ORM\Lazy
     * @var RecipientList
     */
    protected $recipientList;

    /**
     * UID of the bounce account. Only exist for ease of use with ExtJS
     *
     * @var int
     */
    protected $uidRecipientList;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set default values for new newsletter
        $this->setPlannedTime(new DateTime());
    }

    /**
     * Setter for uid
     *
     * @param int $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Setter for plannedTime
     *
     * @param DateTime $plannedTime When the newsletter will start sending emails
     */
    public function setPlannedTime(DateTime $plannedTime)
    {
        $this->plannedTime = $plannedTime;
    }

    /**
     * Getter for plannedTime
     *
     * @return DateTime When the newsletter will start sending emails
     */
    public function getPlannedTime()
    {
        return $this->plannedTime;
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
     * Setter for repetition
     *
     * @param int $repetition 0-7 values to indicates when this newsletter will repeat
     */
    public function setRepetition($repetition)
    {
        $this->repetition = $repetition;
    }

    /**
     * Getter for repetition
     *
     * @return int 0-7 values to indicates when this newsletter will repeat
     */
    public function getRepetition()
    {
        return $this->repetition;
    }

    /**
     * Setter for plainConverter
     *
     * @param string $plainConverter Tool used to convert to plain text
     */
    public function setPlainConverter($plainConverter)
    {
        $this->plainConverter = $plainConverter;
    }

    /**
     * Getter for plainConverter
     *
     * @return string Tool used to convert to plain text
     */
    public function getPlainConverter()
    {
        return $this->plainConverter;
    }

    /**
     * Returns an instance of plain converter
     *
     * @return IPlainConverter
     * @throws \Exception
     */
    public function getPlainConverterInstance()
    {
        $class = $this->getPlainConverter();

        // Instantiate converter or fallback to builtin
        if (class_exists($class)) {
            $converter = new $class();
        } else {
            $converter = new PlainConverter\Builtin();
        }

        if (!($converter instanceof IPlainConverter)) {
            throw new \Exception("$class does not implement \Mirko\Newsletter\Domain\Model\IPlainConverter");
        }

        return $converter;
    }

    /**
     * Setter for isTest
     *
     * @param bool $isTest Whether this newsletter is for test purpose. If it is it will be ignored in statistics
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;
    }

    /**
     * Getter for isTest
     *
     * @return bool Whether this newsletter is for test purpose. If it is it will be ignored in statistics
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * Returns the state of isTest
     *
     * @return bool the state of isTest
     */
    public function isIsTest()
    {
        return $this->getIsTest();
    }

    /**
     * Setter for attachments
     *
     * @param string[] $attachments List of files to be attached (comma separated list
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = implode(',', $attachments);
    }

    /**
     * Getter for attachments
     *
     * @return string[] List of files to be attached (comma separated list
     */
    public function getAttachments()
    {
        return explode(',', $this->attachments);
    }

    /**
     * Setter for senderName
     *
     * @param string $senderName The name of the newsletter sender
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    }

    /**
     * Gets the correct sendername for a newsletter.
     * This is either:
     * The sender name defined on the newsletter record.
     * or the sender name defined in $TYPO3_CONF_VARS['EXTCONF']['newsletter']['senderName']
     * or The sites name as defined in $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename']
     *
     * @return string The name of the newsletter sender
     */
    public function getSenderName()
    {
        $db = Tools::getDatabaseConnection();

        // Return the senderName defined on the newsletter
        if ($this->senderName) {
            return $this->senderName;
        }

        // Return the senderName defined in extension configuration
        $sender = Tools::confParam('sender_name');
        if ($sender === 'user') {
            // Use the page-owner as user
            $rs = $db->sql_query(
                "SELECT realName
							  FROM be_users
							  LEFT JOIN pages ON be_users.uid = pages.perms_userid
							  WHERE pages.uid = $this->pid"
            );

            [$sender] = $db->sql_fetch_row($rs);
            if ($sender) {
                return $sender;
            }
        } // Returns the name as defined in configuration
        elseif ($sender) {
            return $sender;
        }

        // If none of above, just use the sitename
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'];
    }

    /**
     * Setter for senderEmail
     *
     * @param string $senderEmail The email of the newsletter sender
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;
    }

    /**
     * Gets the correct sender email address for a newsletter.
     * This is either:
     * The sender email address defined on the page record.
     * or the email address (if any) of the be_user owning the page.
     * or the email address defined in extConf
     * or the guessed email address of the user running the this process.
     * or the no-reply@$_SERVER['HTTP_HOST'].
     *
     * @return string The email of the newsletter sender
     */
    public function getSenderEmail()
    {
        $db = Tools::getDatabaseConnection();

        /* The sender defined on the page? */
        if (GeneralUtility::validEmail($this->senderEmail)) {
            return $this->senderEmail;
        }

        /* Anything in typo3_conf_vars? */
        $email = Tools::confParam('sender_email');
        if ($email === 'user') {
            /* Use the page-owner as user */
            $rs = $db->sql_query(
                "SELECT email
			FROM be_users bu
			LEFT JOIN pages p ON bu.uid = p.perms_userid
			WHERE p.uid = $this->pid"
            );

            [$email] = Tools::getDatabaseConnection()->sql_fetch_row($rs);
            if (GeneralUtility::validEmail($email)) {
                return $email;
            }
        }

        /* Maybe it was a hardcoded email address? */
        if (GeneralUtility::validEmail($email)) {
            return $email;
        }

        return trim(exec('whoami')) . '@' . trim(exec('hostname'));
    }

    /**
     * Setter for Reply-To: name
     *
     * @param string $replytoName
     */
    public function setReplytoName($replytoName)
    {
        $this->replytoName = $replytoName;
    }

    /**
     * Getter for Reply-To: name
     *
     * @return string
     */
    public function getReplytoName()
    {
        // Return the replytoName defined on the newsletter
        if ($this->replytoName) {
            return $this->replytoName;
        }

        // Return the replytoName defined in extension configuration
        $replytoName = Tools::confParam('replyto_name');
        if ($replytoName) {
            return $replytoName;
        }

        // Return empty
        return '';
    }

    /**
     * Setter for Reply-To: <email>
     *
     * @param string $replytoEmail
     */
    public function setReplytoEmail($replytoEmail)
    {
        $this->replytoEmail = $replytoEmail;
    }

    /**
     * Getter for Reply-To: <email>
     *
     * @return string
     */
    public function getReplytoEmail()
    {
        // Return the replytoEmail defined on the newsletter
        if (GeneralUtility::validEmail($this->replytoEmail)) {
            return $this->replytoEmail;
        }

        // Return the replytoEmail defined in extension configuration
        $replyToEmail = Tools::confParam('replyto_email');
        if (GeneralUtility::validEmail($replyToEmail)) {
            return $replyToEmail;
        }

        // Return empty
        return '';
    }

    /**
     * Setter for injectOpenSpy
     *
     * @param bool $injectOpenSpy injectOpenSpy
     */
    public function setInjectOpenSpy($injectOpenSpy)
    {
        $this->injectOpenSpy = $injectOpenSpy;
    }

    /**
     * Getter for injectOpenSpy
     *
     * @return bool injectOpenSpy
     */
    public function getInjectOpenSpy()
    {
        return $this->injectOpenSpy;
    }

    /**
     * Returns the state of injectOpenSpy
     *
     * @return bool the state of injectOpenSpy
     */
    public function isInjectOpenSpy()
    {
        return $this->getInjectOpenSpy();
    }

    /**
     * Setter for injectLinksSpy
     *
     * @param bool $injectLinksSpy injectLinksSpy
     */
    public function setInjectLinksSpy($injectLinksSpy)
    {
        $this->injectLinksSpy = $injectLinksSpy;
    }

    /**
     * Getter for injectLinksSpy
     *
     * @return bool injectLinksSpy
     */
    public function getInjectLinksSpy()
    {
        return $this->injectLinksSpy;
    }

    /**
     * Returns the state of injectLinksSpy
     *
     * @return bool the state of injectLinksSpy
     */
    public function isInjectLinksSpy()
    {
        return $this->getInjectLinksSpy();
    }

    /**
     * Setter for bounceAccount
     *
     * @param BounceAccount $bounceAccount bounceAccount
     */
    public function setBounceAccount(BounceAccount $bounceAccount = null)
    {
        $this->bounceAccount = $bounceAccount;
    }

    /**
     * Getter for bounceAccount's UID
     *
     * @return int uidBounceAccount
     */
    public function getUidBounceAccount()
    {
        $bounceAccount = $this->getBounceAccount();
        if ($bounceAccount) {
            return $bounceAccount->getUid();
        }
    }

    /**
     * Setter for bounceAccount's UID
     *
     * @param int $uidBounceAccount
     */
    public function setUidBounceAccount($uidBounceAccount = null)
    {
        $bounceAccountRepository = GeneralUtility::makeInstance(BounceAccountRepository::class);
        $bounceAccount = $bounceAccountRepository->findByUid($uidBounceAccount);
        $this->setBounceAccount($bounceAccount);
    }

    /**
     * Getter for bounceAccount
     *
     * @return BounceAccount bounceAccount
     */
    public function getBounceAccount()
    {
        return $this->bounceAccount;
    }

    /**
     * Setter for recipientList
     *
     * @param RecipientList $recipientList recipientList
     */
    public function setRecipientList(RecipientList $recipientList)
    {
        $this->recipientList = $recipientList;
    }

    /**
     * Getter for recipientList
     *
     * @return RecipientList recipientList
     */
    public function getRecipientList()
    {
        return $this->recipientList;
    }

    /**
     * Getter for recipientList's UID
     *
     * @return int uidRecipientList
     */
    public function getUidRecipientList()
    {
        $recipientList = $this->getRecipientList();
        if ($recipientList) {
            return $recipientList->getUid();
        }
    }

    /**
     * Setter for recipientList's UID
     *
     * @param int $uidRecipientList
     */
    public function setUidRecipientList($uidRecipientList)
    {
        $recipientListRepository = GeneralUtility::makeInstance(RecipientListRepository::class);
        $recipientList = $recipientListRepository->findByUid($uidRecipientList);
        $this->setRecipientList($recipientList);
    }


    /**
     * Returns the title, NOT localized, of the page sent by this newsletter.
     * This should only used for BE, because newsletter recipients need localized title
     *
     * @return string the title
     */
    public function getTitle()
    {
        $db = Tools::getDatabaseConnection();
        $rs = $db->sql_query("SELECT title FROM pages WHERE uid = $this->pid");

        $title = '';
        if ($db->sql_num_rows($rs)) {
            list($title) = $db->sql_fetch_row($rs);
        }

        return $title;
    }

    /**
     * Set the validator
     *
     * @param Validator $validor
     */
    public function setValidator(Validator $validor)
    {
        $this->validator = $validor;
    }

    /**
     * Get the validator
     *
     * @return Validator
     */
    public function getValidator()
    {
        if (!$this->validator) {
            $this->validator = new Validator();
        }

        return $this->validator;
    }

    /**
     * Returns the content of this newsletter with validation messages. The content
     * is also "fixed" automatically when possible.
     *
     * @param string $language language of the content of the newsletter (the 'L' parameter in TYPO3 URL)
     *
     * @return array ('content' => $content, 'errors' => $errors, 'warnings' => $warnings, 'infos' => $infos);
     */
    public function getValidatedContent($language = null)
    {
        return $this->getValidator()->validate($this, $language);
    }


    public function getStatus()
    {
        return GeneralUtility::makeInstance(NewsletterService::class)->getStatus($this);
    }

    /**
     * @return array
     */
    public function getStatistics()
    {
        return GeneralUtility::makeInstance(NewsletterService::class)->getStatistics($this);
    }
}
