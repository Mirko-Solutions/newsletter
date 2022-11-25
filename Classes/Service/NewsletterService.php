<?php

namespace Mirko\Newsletter\Service;

use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\EmailRepository;
use Mirko\Newsletter\Domain\Repository\LinkRepository;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NewsletterService
{
    private EmailRepository $emailRepository;

    private NewsletterRepository $newsletterRepository;

    private LinkRepository $linkRepository;

    public function __construct(
        EmailRepository $emailRepository,
        NewsletterRepository $newsletterRepository,
        LinkRepository $linkRepository
    ) {
        $this->emailRepository = $emailRepository;
        $this->newsletterRepository = $newsletterRepository;
        $this->linkRepository = $linkRepository;
    }

    public static function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Returns the count of recipient to which the newsletter was actually sent (or going to be sent if the process is not finished yet).
     * This may differ from $newsletter->getRecipientList()->getCount()
     * because the recipientList may change over time.
     */
    public function getEmailCount(Newsletter $newsletter)
    {
        // If the newsletter didn't start, we rely on recipientList to tell us how many email there will be
        if (!$newsletter->getBeginTime()) {
            $recipientList = $newsletter->getRecipientList();
            $recipientList->init();

            return $recipientList->getCount();
        }

        return $this->emailRepository->getCount($newsletter->getUid());
    }

    /**
     * Get the number of not yet sent email
     */
    public function getEmailNotSentCount(Newsletter $newsletter)
    {
        $queryBuilder = Tools::getQueryBuilderForTable('tx_newsletter_domain_model_email');

        // If the newsletter didn't start, then it means all emails are "not sent"
        if (!$newsletter->getBeginTime()) {
            return $this->getEmailCount($newsletter);
        }
        $numberOfNotSent = $queryBuilder
            ->select('uid')
            ->from('tx_newsletter_domain_model_email')
            ->where($queryBuilder->expr()->eq('end_time', $queryBuilder->createNamedParameter(0)))
            ->andWhere(
                $queryBuilder->expr()->eq('newsletter', $queryBuilder->createNamedParameter($newsletter->getUid()))
            )
            ->execute()->rowCount();
        return (int)$numberOfNotSent;
    }

    /**
     * Returns the URL of the content of this newsletter
     *
     * @param int $language
     *
     * @return string
     */
    public static function getContentUrl(Newsletter $newsletter, string $language = null): string
    {
        $append_url = Tools::confParam('append_url');
        $baseUrl = Tools::getBaseUrl();

        if (!is_null($language)) {
            $language = '&L=' . $language;
        }

        return $baseUrl . '/index.php?id=' . $newsletter->getPid() . $language . $append_url;
    }

    /**
     * Schedule the next newsletter if it defined to be repeated
     */
    public function scheduleNextNewsletter(Newsletter $newsletter): void
    {
        $plannedTime = $newsletter->getPlannedTime();
        [$year, $month, $day, $hour, $minute] = explode('-', date('Y-n-j-G-i', $plannedTime->format('U')));

        switch ($newsletter->getRepetition()) {
            case 0:
                return;
            case 1:
                ++$day;
                break;
            case 2:
                $day += 7;
                break;
            case 3:
                $day += 14;
                break;
            case 4:
                ++$month;
                break;
            case 5:
                $month += 3;
                break;
            case 6:
                $month += 6;
                break;
            case 7:
                ++$year;
                break;
        }
        $newPlannedTime = mktime($hour, $minute, 0, $month, $day, $year);
        $newsletter->setPlannedTime(new \DateTime($newPlannedTime));
        $this->newsletterRepository->add($newsletter);
    }

    /**
     * Return a human readable status for the newsletter
     *
     * @return string
     */
    public function getStatus(Newsletter $newsletter): string
    {
        // Here we need to include the locallization file for ExtDirect calls, otherwise we get empty strings
        global $LANG;
        $LANG->includeLLFile('EXT:newsletter/Resources/Private/Language/locallang.xlf');

        $plannedTime = $newsletter->getPlannedTime();
        $beginTime = $newsletter->getBeginTime();
        $endTime = $newsletter->getEndTime();

        // If we don't have a valid UID, it means we are a "fake model" newsletter not saved yet
        if (!($newsletter->getUid() > 0)) {
            return $LANG->getLL('newsletter_status_not_planned');
        }

        if ($plannedTime && !$beginTime) {
            return sprintf($LANG->getLL('newsletter_status_planned'), $plannedTime->format('D, d M y H:i:s'));
        }

        if ($beginTime && !$endTime) {
            return $LANG->getLL('newsletter_status_generating_emails');
        }

        if ($beginTime && $endTime) {
            $emailCount = $this->getEmailCount($newsletter);
            $emailNotSentCount = $this->getEmailNotSentCount($newsletter);

            if ($emailNotSentCount) {
                return sprintf(
                    $LANG->getLL('newsletter_status_sending'),
                    $emailCount - $emailNotSentCount,
                    $emailCount
                );
            }

            return sprintf($LANG->getLL('newsletter_status_was_sent'), $endTime->format('D, d M y H:i:s'));
        }

        return 'unexpected status';
    }

    /**
     * Returns newsletter statistics to be used for pie and timeline chart
     *
     * @return array eg: array(array(time, emailNotSentCount, emailSentCount, emailOpenedCount, emailBouncedCount, emailCount, linkOpenedCount, linkCount, [and same fields but Percentage instead of Count] ))
     */
    public function getStatistics(Newsletter $newsletter): array
    {

        $uidNewsletter = $newsletter->getUid();

        $stateDifferences = [];
        $emailCount = $this->newsletterRepository->fillStateDifferences(
            $stateDifferences,
            'tx_newsletter_domain_model_email',
            'newsletter = ' . $uidNewsletter,
            [
                'end_time' => ['increment' => 'emailSentCount', 'decrement' => 'emailNotSentCount'],
                'open_time' => ['increment' => 'emailOpenedCount', 'decrement' => 'emailSentCount'],
                'bounce_time' => ['increment' => 'emailBouncedCount', 'decrement' => 'emailSentCount'],
            ]
        );

        $linkCount = $this->linkRepository->getCount($uidNewsletter);

        $this->newsletterRepository->fillStateDifferences(
            $stateDifferences,
            'tx_newsletter_domain_model_link LEFT JOIN tx_newsletter_domain_model_linkopened ON (tx_newsletter_domain_model_linkopened.link = tx_newsletter_domain_model_link.uid)',
            'tx_newsletter_domain_model_link.newsletter = ' . $uidNewsletter,
            [
                'open_time' => ['increment' => 'linkOpenedCount'],
            ]
        );

        // Find out the very first event (when the newsletter was planned)
        $plannedTime = $newsletter->getPlannedTime();
        // We re-calculate email count so get correct number if newsletter is not sent yet
        $emailCount = $this->getEmailCount($newsletter) ?? $emailCount;
        $previousState = [
            'time' => $plannedTime ? (int)$plannedTime->format('U') : null,
            'emailNotSentCount' => $emailCount,
            'emailSentCount' => 0,
            'emailOpenedCount' => 0,
            'emailBouncedCount' => 0,
            'emailCount' => $emailCount,
            'linkOpenedCount' => 0,
            'linkCount' => $linkCount,
            'emailNotSentPercentage' => 100,
            'emailSentPercentage' => 0,
            'emailOpenedPercentage' => 0,
            'emailBouncedPercentage' => 0,
            'linkOpenedPercentage' => 0,
        ];

        // Find out what the best grouping step is according to number of states
        $stateCount = count($stateDifferences);
        if ($stateCount > 5000) {
            $groupingTimestamp = 15 * 60; // 15 minutes
        } elseif ($stateCount > 500) {
            $groupingTimestamp = 5 * 60; // 5 minutes
        } elseif ($stateCount > 50) {
            $groupingTimestamp = 1 * 60; // 1 minutes
        } else {
            $groupingTimestamp = 0; // no grouping at all
        }

        $states = [$previousState];
        ksort($stateDifferences);
        $minimumTimeToInsert = 0; // First state must always be not grouped, so we don't increment here
        foreach ($stateDifferences as $time => $diff) {
            $newState = $previousState;
            $newState['time'] = $time;

            // Apply diff to previous state to get new state's absolute values
            foreach ($diff as $key => $value) {
                $newState[$key] += $value;
            }

            // Compute percentage for email states
            foreach (['emailNotSent', 'emailSent', 'emailOpened', 'emailBounced'] as $key) {
                $newState[$key . 'Percentage'] = round($newState[$key . 'Count'] / $newState['emailCount'] * 100, 1);
            }

            // Compute percentage for link states
            if ($newState['linkCount'] && $newState['emailCount']) {
                $newState['linkOpenedPercentage'] = round(
                    $newState['linkOpenedCount'] / ($newState['linkCount'] * $newState['emailCount']) * 100,
                    1
                );
            } else {
                $newState['linkOpenedPercentage'] = 0;
            }

            // Insert the state only if grouping allows it
            if ($time >= $minimumTimeToInsert) {
                $states[] = $newState;
                $minimumTimeToInsert = $time + $groupingTimestamp;
            }
            $previousState = $newState;
        }

        return $states;
    }
}