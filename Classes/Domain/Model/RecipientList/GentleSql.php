<?php

namespace Mirko\Newsletter\Domain\Model\RecipientList;

use Mirko\Newsletter\Tools;
use Mirko\Newsletter\Utility\EmailParser;

/**
 * This is a more gentle version on the generic sql-driven target. It is dependant on integer field tx_newsletter_bounce
 * on the $this->getTableName() table.
 */
abstract class GentleSql extends Sql
{
    /**
     * Returns the tablename to work with
     *
     * @return string
     */
    abstract protected function getTableName();

    /**
     * This increases the bounce-counter each time a mail has bounced.
     * Hard bounces count more that soft ones. After 2 hards or 10 softs the user will be disabled.
     * You should be able to reset then in the backend
     *
     * @param string $email the email address of the recipient
     * @param int $bounceLevel this is the level of the bounce
     *
     * @return bool success of the bounce-handling
     */
    public function registerBounce($email, $bounceLevel)
    {
        $increment = 0;
        switch ($bounceLevel) {
            case EmailParser::NEWSLETTER_UNSUBSCRIBE:
                $increment = 10;
                break;
            case EmailParser::NEWSLETTER_HARDBOUNCE:
                $increment = 5;
                break;
            case EmailParser::NEWSLETTER_SOFTBOUNCE:
                $increment = 1;
                break;
        }

        if ($increment) {
            return Tools::executeRawDBQuery(
                'UPDATE ' . $this->getTableName() . "
						SET tx_newsletter_bounce = tx_newsletter_bounce + $increment
						WHERE email = '$email'"
            )->rowCount();
        }

        return false;
    }

    /**
     * This is a default action for registered clicks.
     * Here we just reset the bounce counter. If the user reads the mail, it must have succeded.
     * It can also be used for marketing or statistics purposes
     *
     * @param string $email the email address of the recipient
     */
    public function registerClick($email)
    {
        Tools::executeRawDBQuery(
            'UPDATE ' . $this->getTableName() . "
							SET tx_newsletter_bounce = 0
							WHERE email = '{$email}'"
        );
    }

    /**
     * Like the registerClick()-method, but just for embedded spy-image.
     *
     * @param string $email the email address of the recipient
     */
    public function registerOpen($email)
    {
        Tools::executeRawDBQuery(
            'UPDATE ' . $this->getTableName() . "
							SET tx_newsletter_bounce = 0
							WHERE email = '$email'"
        );
    }
}
