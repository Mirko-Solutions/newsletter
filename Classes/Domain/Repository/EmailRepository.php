<?php

namespace Mirko\Newsletter\Domain\Repository;

use Mirko\Newsletter\Domain\Model\Email;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for \Mirko\Newsletter\Domain\Model\Email
 */
class EmailRepository extends AbstractRepository
{
    protected static array $emailCountCache = [];

    /**
     * Returns the email corresponsding to the authCode
     *
     * @param string $authcode
     *
     * @return Email
     */
    public function findByAuthcode($authcode)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('auth_code', $authcode));
        $query->setLimit(1);
        return $query->execute()->getFirst();
    }

    /**
     * Returns the count of emails for a given newsletter
     *
     * @param int $uidNewsletter
     */
    public function getCount(int $uidNewsletter)
    {
        // If we have cached result return directly that value to avoid X query for X Links per newsletter
        if (isset(self::$emailCountCache[$uidNewsletter])) {
            return self::$emailCountCache[$uidNewsletter];
        }

        $query = $this->createQuery();
        $count = $query->matching($query->equals('newsletter', $uidNewsletter))->execute()->count();

        self::$emailCountCache[$uidNewsletter] = $count;

        return $count;
    }

    /**
     * Returns all email for a given newsletter
     *
     * @param int $uidNewsletter
     * @param int $start
     * @param int $limit
     *
     * @return Email[]
     */
    public function findAllByNewsletter($uidNewsletter, $start, $limit)
    {
        if ($uidNewsletter < 1) {
            return $this->findAll();
        }

        $query = $this->createQuery();
        $query->matching($query->equals('newsletter', $uidNewsletter));
        $query->setLimit($limit);
        $query->setOffset($start);
        return $query->execute();
    }

    /**
     * Register an open email in database and forward the event to RecipientList
     * so it can optionally do something more
     *
     * @param string $authCode
     */
    public function registerOpen($authCode)
    {
        $updateEmailCount = Tools::executeRawDBQuery(
            'UPDATE tx_newsletter_domain_model_email SET open_time = ' . time(
            ) . " WHERE open_time = 0 AND auth_code = '{$authCode}'"
        )->rowCount();
        // Tell the target that he opened the email, but only the first time
        if ($updateEmailCount) {
            $rs = Tools::executeRawDBQuery(
                "
            SELECT tx_newsletter_domain_model_newsletter.recipient_list, tx_newsletter_domain_model_email.recipient_address
            FROM tx_newsletter_domain_model_email
            LEFT JOIN tx_newsletter_domain_model_newsletter ON (tx_newsletter_domain_model_email.newsletter = tx_newsletter_domain_model_newsletter.uid)
            LEFT JOIN tx_newsletter_domain_model_recipientlist ON (tx_newsletter_domain_model_newsletter.recipient_list = tx_newsletter_domain_model_recipientlist.uid)
            WHERE tx_newsletter_domain_model_email.auth_code = '{$authCode}' AND recipient_list IS NOT NULL
            LIMIT 1"
            );

            if ([$recipientListUid, $emailAddress] = $rs->fetchNumeric()) {

                $recipientListRepository = GeneralUtility::makeInstance(RecipientListRepository::class);
                $recipientList = $recipientListRepository->findByUid($recipientListUid);
                if ($recipientList) {
                    $recipientList->registerOpen($emailAddress);
                }
            }
        }
    }
}
