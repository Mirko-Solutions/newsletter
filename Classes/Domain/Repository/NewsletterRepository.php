<?php

namespace Mirko\Newsletter\Domain\Repository;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Repository for \Mirko\Newsletter\Domain\Model\Newsletter
 */
class NewsletterRepository extends AbstractRepository
{
    /**
     * Returns the latest newsletter for the given page
     *
     * @param int $pid
     */
    public function getLatest($pid)
    {
        $query = $this->createQuery();
        $query->setLimit(1);
        $query->matching($query->equals('pid', $pid));

        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute()->getFirst();
    }

    /**
     * @param int $pid
     */
    public function findAllByPid($pid)
    {
        if ($pid < 1) {
            return $this->findAll();
        }

        $query = $this->createQuery();
        $query->matching($query->equals('pid', $pid));

        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    /**
     * Returns all newsletter which are ready to be sent now and not yet locked (sending already started)
     *
     * @return Newsletter[]
     */
    public function findAllReadyToSend()
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->lessThanOrEqual('plannedTime', time()),
                $query->logicalNot($query->equals('plannedTime', 0)),
                $query->equals('beginTime', 0)
            )
        );

        return $query->execute();
    }

    /**
     * Returns all newsletter which are currently being sent
     *
     * @return Newsletter[]
     */
    public function findAllBeingSent()
    {
        $query = $this->createQuery();
        $query->statement(
            'SELECT * FROM `tx_newsletter_domain_model_newsletter` WHERE uid IN (SELECT newsletter FROM `tx_newsletter_domain_model_email` WHERE end_time = 0)'
        );

        return $query->execute()->toArray();
    }

    /**
     * Fills the $stateDifferences array with incremental difference that the state introduce.
     * It supports merging with existing diff in the array and several states on the same time.
     *
     * @param array $stateDifferences
     * @param string $from
     * @param string $where
     * @param array $stateConfiguration
     *
     * @return int count of records (not count of states)
     */
    public function fillStateDifferences(array &$stateDifferences, $from, $where, array $stateConfiguration)
    {
        $default = [
            'emailNotSentCount' => 0,
            'emailSentCount' => 0,
            'emailOpenedCount' => 0,
            'emailBouncedCount' => 0,
            'linkOpenedCount' => 0,
        ];

        $columns = implode(', ', array_keys($stateConfiguration));
        $rs = Tools::executeRawDBQuery("SELECT {$columns} FROM {$from} WHERE {$where}");

        $count = 0;
        while ($email = $rs->fetchAssociative()) {
            foreach ($stateConfiguration as $stateKey => $stateConf) {
                $time = $email[$stateKey];
                if ($time) {
                    if (!isset($stateDifferences[$time])) {
                        $stateDifferences[$time] = $default;
                    }

                    ++$stateDifferences[$time][$stateConf['increment']];
                    if (isset($stateConf['decrement'])) {
                        --$stateDifferences[$time][$stateConf['decrement']];
                    }
                }
            }
            ++$count;
        }

        return $count;
    }

    /**
     * Find all pairs of newsletter-email UIDs that are should be sent
     *
     * @param Newsletter $newsletter
     *
     * @return array [[newsletter => 12, email => 5], ...]
     */
    public static function findAllNewsletterAndEmailUidToSend(Newsletter $newsletter = null)
    {
        // Apply limit of emails per round
        $mails_per_round = (int)Tools::confParam('mails_per_round');
        if ($mails_per_round) {
            $limit = ' LIMIT ' . $mails_per_round;
        } else {
            $limit = '';
        }

        // Apply newsletter restriction if any
        if ($newsletter) {
            $newsletterUid = 'AND tx_newsletter_domain_model_newsletter.uid = ' . $newsletter->getUid();
        } else {
            $newsletterUid = '';
        }

        // Find the uid of emails and newsletters that need to be sent
        return Tools::executeRawDBQuery(
            'SELECT tx_newsletter_domain_model_newsletter.uid AS newsletter, tx_newsletter_domain_model_email.uid AS email
						FROM tx_newsletter_domain_model_email
						INNER JOIN tx_newsletter_domain_model_newsletter ON (tx_newsletter_domain_model_email.newsletter = tx_newsletter_domain_model_newsletter.uid)
						WHERE tx_newsletter_domain_model_email.begin_time = 0
                        ' . $newsletterUid . '
						ORDER BY tx_newsletter_domain_model_email.newsletter ' . $limit
        )->fetchAllAssociative();
    }
}
