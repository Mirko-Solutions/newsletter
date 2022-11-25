<?php

namespace Mirko\Newsletter\Domain\Model\RecipientList;

use Mirko\Newsletter\Domain\Model\RecipientList;
use Mirko\Newsletter\Tools;
use Mirko\Newsletter\Utility\EmailParser;

/**
 * This is the basic SQL related newsletter target. Methods implemented with DB calls using SQL query defined by end-user.
 * Extend this class to create newsletter targets which extracts recipients from the database.
 */
class Sql extends RecipientList
{
    /**
     * sqlStatement
     *
     * @var string
     */
    protected $sqlStatement = '';

    /**
     * sqlRegisterBounce
     *
     * @var string
     */
    protected $sqlRegisterBounce = '';

    /**
     * sqlRegisterOpen
     *
     * @var string
     */
    protected $sqlRegisterOpen = '';

    /**
     * sqlRegisterClick
     *
     * @var string
     */
    protected $sqlRegisterClick = '';

    /**
     * Setter for sqlStatement
     *
     * @param string $sqlStatement sqlStatement
     */
    public function setSqlStatement($sqlStatement)
    {
        $this->sqlStatement = $sqlStatement;
    }

    /**
     * Getter for sqlStatement
     *
     * @return string sqlStatement
     */
    public function getSqlStatement()
    {
        return $this->sqlStatement;
    }

    /**
     * Setter for sqlRegisterBounce
     *
     * @param string $sqlRegisterBounce sqlRegisterBounce
     */
    public function setSqlRegisterBounce($sqlRegisterBounce)
    {
        $this->sqlRegisterBounce = $sqlRegisterBounce;
    }

    /**
     * Getter for sqlRegisterBounce
     *
     * @return string sqlRegisterBounce
     */
    public function getSqlRegisterBounce()
    {
        return $this->sqlRegisterBounce;
    }

    /**
     * Setter for sqlRegisterOpen
     *
     * @param string $sqlRegisterOpen sqlRegisterOpen
     */
    public function setSqlRegisterOpen($sqlRegisterOpen)
    {
        $this->sqlRegisterOpen = $sqlRegisterOpen;
    }

    /**
     * Getter for sqlRegisterOpen
     *
     * @return string sqlRegisterOpen
     */
    public function getSqlRegisterOpen()
    {
        return $this->sqlRegisterOpen;
    }

    /**
     * Setter for sqlRegisterClick
     *
     * @param string $sqlRegisterClick sqlRegisterClick
     */
    public function setSqlRegisterClick($sqlRegisterClick)
    {
        $this->sqlRegisterClick = $sqlRegisterClick;
    }

    /**
     * Getter for sqlRegisterClick
     *
     * @return string sqlRegisterClick
     */
    public function getSqlRegisterClick()
    {
        return $this->sqlRegisterClick;
    }

    public function init()
    {
        $sql = trim($this->getSqlStatement());

        // Inject dummy SQL statement, just for fun !
        if (!$sql) {
            $sql = 'SELECT email FROM be_users';
        }

        $this->data = Tools::executeRawDBQuery($sql);
    }

    /**
     * Fetch a recipient from the sql-record set. This also computes some commonly used values,
     * such as plain_only and language.
     *
     * @return array|false recipient with user data
     */
    public function getRecipient()
    {
        $r = $this->data->fetchAssociative();
        if (is_array($r)) {
            if (!isset($r['plain_only'])) {
                $r['plain_only'] = $this->isPlainOnly();
            }

            if (!isset($r['L'])) {
                $r['L'] = $this->getLang();
            }

            return $r;
        }

        return false;
    }

    public function getCount()
    {
        return $this->data->rowCount();
    }

    public function getError()
    {
        return $this->data->errorInfo();
    }

    /**
     * Execute the SQL defined by the user to disable a recipient.
     *
     * @param string $email the email address of the recipient
     * @param int $bounceLevel Level of bounce, @see \Mirko\Newsletter\BounceHandler for possible values
     *
     * @return bool status of the success of the removal
     */
    public function registerBounce($email, $bounceLevel)
    {
        $sql = str_replace([
            '###EMAIL###',
            '###BOUNCE_TYPE###',
            '###BOUNCE_TYPE_SOFT###',
            '###BOUNCE_TYPE_HARD###',
            '###BOUNCE_TYPE_UNSUBSCRIBE###',
        ], [
            "'{$email}'",
            $bounceLevel,
            EmailParser::NEWSLETTER_SOFTBOUNCE,
            EmailParser::NEWSLETTER_HARDBOUNCE,
            EmailParser::NEWSLETTER_UNSUBSCRIBE,
        ], $this->getSqlRegisterBounce());

        if ($sql) {
            return Tools::executeRawDBQuery($sql)->rowCount();
        }

        return false;
    }

    /**
     * Execute the SQL defined by the user to register that the email was open
     *
     * @param string $email the email address of the recipient (who opened the mail)
     */
    public function registerOpen($email)
    {
        $sql = str_replace('###EMAIL###', "'{$email}'", $this->getSqlRegisterOpen());

        if ($sql) {
           Tools::executeRawDBQuery($sql);
        }
    }

    /**
     * Execute the SQL defined by the user to whenever the recipient has clicked a link via click.php
     *
     * @param string $email the email address of the recipient
     */
    public function registerClick($email)
    {
        $sql = str_replace('###EMAIL###', "'{$email}'", $this->getSqlRegisterClick());

        if ($sql) {
            Tools::executeRawDBQuery($sql);
        }
    }
}
