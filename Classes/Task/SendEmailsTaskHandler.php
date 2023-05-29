<?php
declare(strict_types=1);

namespace Mirko\Newsletter\Task;

use Mirko\Newsletter\Tools;
use Psr\Log\LoggerInterface;
use Throwable;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function bin2hex;
use function gmdate;
use function random_bytes;
use function sprintf;
use function substr;

class SendEmailsTaskHandler
{
    private const REGISTRY_NAMESPACE = 'newsletter';
    private const REGISTRY_KEY = 'newsletterSpoolRunId';

    private Registry $registry;

    private LoggerInterface $logger;

    private string $runId;

    public function __construct()
    {
        $this->registry = GeneralUtility::makeInstance(Registry::class);
        $this->logger = Tools::getLogger(__CLASS__);
        $this->runId = substr(bin2hex(random_bytes(20)), 0, 12) . ' ' . gmdate('H:i:s');
    }

    public function createAndRunSpool(): bool
    {
        // Check if the create-and-run-spool function is "locked"
        $currentlyActiveRunId = $this->registry->get(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY);
        if ($currentlyActiveRunId !== null) {
            $this->logger->error(
                sprintf('Another "send-emails" scheduler task is already running (Run ID: #%s)', $currentlyActiveRunId)
            );

            return false;
        }

        // Lock the create-and-run-spool function
        $this->registry->set(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY, $this->runId);
        try {
            return $this->tryCreateAndRunSpool();
        } finally {
            // Release the lock on the create-and-run-spool function
            $this->registry->remove(self::REGISTRY_NAMESPACE, self::REGISTRY_KEY);
        }
    }

    private function tryCreateAndRunSpool(): bool
    {
        $tools = Tools::getInstance();
        try {
            $this->logger->debug('Create all spool');
            $tools->createAllSpool();
        } catch (Throwable $e) {
            // Log and re-throw the error
            $this->logger->error(
                sprintf('Caught exception #%d while creating spool: %s', $e->getCode(), $e->getMessage())
            );
            throw $e;
        }
        try {
            $this->logger->debug('Will run all spool');
            $tools->runAllSpool();
            $this->logger->debug('Did run all spool');
        } catch (Throwable $e) {
            // Log and re-throw the error
            $this->logger->error(
                sprintf('Caught exception #%d while running spool: %s', $e->getCode(), $e->getMessage())
            );
            throw $e;
        }

        return true;
    }
}
