<?php

namespace Mirko\Newsletter\Task;

use Mirko\Newsletter\Service\NewsletterService;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Provides Scheduler task to send emails
 */
class SendEmails extends AbstractTask
{
    private NewsletterService $newsletterService;

    /**
     * Sends emails for queued newsletter
     *
     * @return bool Returns true on successful execution, false on error
     */
    public function execute()
    {
        $tools = Tools::getInstance();
        $logger = Tools::getLogger(__CLASS__);
        $logger->debug('Create all spool');
        $tools->createAllSpool();
        $logger->debug('Will run all spool');
        $tools->runAllSpool();
        $logger->debug('Did run all spool');

        return true;
    }

    /**
     * This method is designed to return some additional information about the task,
     * that may help to set it apart from other tasks from the same class
     * This additional information is used - for example - in the Scheduler's BE module
     * This method should be implemented in most task classes
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        $newsletterRepository = Tools::getInstance()->getNewsletterRepository();
        $newsletterService = NewsletterService::getInstance();
        $newslettersToSend = $newsletterRepository->findAllReadyToSend();
        $newslettersBeingSent = $newsletterRepository->findAllBeingSent();
        $newslettersToSendCount = count($newslettersToSend);
        $newslettersBeingSentCount = count($newslettersBeingSent);

        $emailNotSentCount = 0;
        foreach ($newslettersToSend as $newsletter) {
            $emailNotSentCount += $newsletterService->getEmailNotSentCount($newsletter);
        }
        foreach ($newslettersBeingSent as $newsletter) {
            $emailNotSentCount += $newsletterService->getEmailNotSentCount($newsletter);
        }

        $emailsPerRound = Tools::confParam('mails_per_round');

        return LocalizationUtility::translate(
            'task_send_emails_additional_information',
            'newsletter',
            [$emailsPerRound, $emailNotSentCount, $newslettersToSendCount, $newslettersBeingSentCount]
        );
    }
}
