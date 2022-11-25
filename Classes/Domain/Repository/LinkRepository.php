<?php

namespace Mirko\Newsletter\Domain\Repository;

use Mirko\Newsletter\Domain\Model\Link;
use Mirko\Newsletter\Tools;
use Mirko\Newsletter\Utility\MarkerSubstitutor;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for \Mirko\Newsletter\Domain\Model\Link
 */
class LinkRepository extends AbstractRepository
{
    /**
     * Returns all links for a given newsletter
     *
     * @param int $uidNewsletter
     * @param int $start
     * @param int $limit
     *
     * @return Link[]
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
     * Returns the count of links for a given newsletter
     *
     * @param int $uidNewsletter
     *
     * @return int
     */
    public function getCount($uidNewsletter)
    {
        $query = $this->createQuery();
        return $query->matching($query->equals('newsletter', $uidNewsletter))->execute()->count();
    }

    /**
     * Register a clicked link in database and forward the event to RecipientList
     * so it can optionally do something more
     *
     * @param int|null $newsletterUid newsletter UID to limit search scope, or NULL
     * @param string $authCode identifier to find back the link
     * @param bool $isPlain
     *
     * @return string|null absolute URL to be redirected to
     */
    public function registerClick($newsletterUid, $authCode, $isPlain)
    {
        $isPlain = $isPlain ? '1' : '0';
        if ($newsletterUid) {
            $limitNewsletter = 'AND tx_newsletter_domain_model_newsletter.uid = ' . (int)$newsletterUid;
        } else {
            $limitNewsletter = '';
        }

        // Attempt to find back records in database based on given authCode
        $rs = Tools::executeRawDBQuery(
            "SELECT tx_newsletter_domain_model_link.uid, tx_newsletter_domain_model_link.url, tx_newsletter_domain_model_email.uid, tx_newsletter_domain_model_newsletter.recipient_list, tx_newsletter_domain_model_email.recipient_address, tx_newsletter_domain_model_email.auth_code
        FROM tx_newsletter_domain_model_newsletter
		INNER JOIN tx_newsletter_domain_model_email ON (tx_newsletter_domain_model_email.newsletter = tx_newsletter_domain_model_newsletter.uid)
		INNER JOIN tx_newsletter_domain_model_link ON (tx_newsletter_domain_model_link.newsletter = tx_newsletter_domain_model_newsletter.uid)
		WHERE
		MD5(CONCAT(tx_newsletter_domain_model_email.auth_code, tx_newsletter_domain_model_link.uid)) = '{$authCode}'
        $limitNewsletter"
        );

        if ([$linkUid, $linkUrl, $emailUid, $recipientListUid, $email, $authCodeEmail] = $rs->fetchNumeric()) {
            // Insert a linkopened record to register which user clicked on which link
            Tools::executeRawDBQuery(
                "
            INSERT INTO tx_newsletter_domain_model_linkopened (link, email, is_plain, open_time)
            VALUES ($linkUid, $emailUid, $isPlain, " . time() . ')
            '
            );
            Tools::executeRawDBQuery(
                "
            UPDATE tx_newsletter_domain_model_link
            SET tx_newsletter_domain_model_link.opened_count = tx_newsletter_domain_model_link.opened_count + 1
            WHERE
            tx_newsletter_domain_model_link.uid = $linkUid
            "
            );
            // Increment the total count of clicks for the link itself (so if the linkopened records are deleted, we still know how many times the link was opened)

            // Also register the email as opened, just in case if it was not already marked open by the open spy (eg: because end-user did not show image)
            $emailRepository = GeneralUtility::makeInstance(EmailRepository::class);
            $emailRepository->registerOpen($authCodeEmail);

            // Forward which user clicked the link to the recipientList so the recipientList may take appropriate action
            $recipientListRepository = GeneralUtility::makeInstance(RecipientListRepository::class);
            $recipientList = $recipientListRepository->findByUid($recipientListUid);
            if ($recipientList) {
                $recipientList->registerClick($email);
            }

            // Finally replace markers that may be present in URL (typically for http://newsletter_view_url, but also any other markers)
            $emailObject = $emailRepository->findByUid($emailUid);
            $substitutor = new MarkerSubstitutor();
            return $substitutor->substituteMarkersInUrl($linkUrl, $emailObject);
        }
    }
}
