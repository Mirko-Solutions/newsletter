<?php

namespace Mirko\Newsletter;

use DateTime;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Result;
use GuzzleHttp\Exception\RequestException;
use Mirko\Newsletter\Domain\Model\Email;
use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\Domain\Repository\EmailRepository;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Service\NewsletterService;
use Mirko\Newsletter\Service\Typo3GeneralService;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use function count;

/**
 * Toolbox for newsletter and dependant extensions.
 */
class Tools
{
    private static array $configuration = [];

    private static string $OPEN_SSL_CIPHER = 'aes-256-cbc';

    private EmailRepository $emailRepository;

    private NewsletterRepository $newsletterRepository;

    private NewsletterService $newsletterService;

    private LoggerInterface $logger;

    public function __construct(
        EmailRepository $emailRepository,
        NewsletterRepository $newsletterRepository,
        NewsletterService $newsletterService
    ) {
        $this->emailRepository = $emailRepository;
        $this->newsletterRepository = $newsletterRepository;
        $this->newsletterService = $newsletterService;
        $this->logger = static::getLogger(__CLASS__);
    }

    public static function getInstance()
    {
        return GeneralUtility::makeInstance(self::class);
    }

    /**
     * Get a newsletter-conf-template parameter
     *
     * @param string $key Parameter key
     *
     * @return mixed Parameter value
     */
    public static function confParam($key)
    {
        // Else fallback to the extension config.
        if (empty(static::$configuration)) {
            static::$configuration = Typo3GeneralService::getExtensionConfiguration();
        }

        return static::$configuration[$key] ?? null;
    }

    /**
     * Returns a logger for given class
     *
     * @param string $class
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    public static function getLogger($class)
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger($class);
    }

    /**
     * Create a configured mailer from a newsletter page record.
     * This mailer will have both plain and HTML content applied as well as files attached.
     *
     * @param Newsletter $newsletter The newsletter
     * @param int        $language
     * @return Mailer preconfigured mailer for sending
     */
    public static function getConfiguredMailer(Newsletter $newsletter, $language = null)
    {
        // Configure the mailer
        $mailer = new Mailer();
        $mailer->setNewsletter($newsletter, $language);

        // hook for modifying the mailer before finish preconfiguring
        if (array_key_exists(
                'getConfiguredMailerHook',
                static::$configuration
            ) && is_array(static::$configuration['getConfiguredMailerHook'])) {
            foreach (static::$configuration['getConfiguredMailerHook'] as $_classRef) {
                $_procObj = GeneralUtility::makeInstance($_classRef);
                $mailer = $_procObj->getConfiguredMailerHook($mailer, $newsletter);
            }
        }

        return $mailer;
    }

    /**
     * Create the spool for all newsletters who need it
     */
    public function createAllSpool()
    {
        $newsletters = $this->newsletterRepository->findAllReadyToSend();
        foreach ($newsletters as $newsletter) {
            $this->createSpool($newsletter);
        }
    }

    /**
     * Spool a newsletter page out to the real receivers.
     *
     * @param Newsletter $newsletter
     */
    public function createSpool(Newsletter $newsletter)
    {
        // If newsletter is locked because spooling now, or already spooled, then skip
        if ($newsletter->getBeginTime()) {
            return;
        }

        // Lock the newsletter by setting its begin_time
        $beginTime = new DateTime();
        $newsletter->setBeginTime($beginTime);
        $this->newsletterRepository->update($newsletter);
        $this->emailRepository->persistAll();

        $emailSpooledCount = 0;
        $recipientList = $newsletter->getRecipientList();
        $recipientList->init();
        while ($receiver = $recipientList->getRecipient()) {
            // Register the recipient
            if (GeneralUtility::validEmail($receiver['email'])) {
                $emailInstance = new Email();
                $emailInstance->setPid($newsletter->getPid());
                $emailInstance->setRecipientData($receiver);
                $emailInstance->setNewsletter($newsletter);
                $this->emailRepository->add($emailInstance);
                $this->emailRepository->persistAll();
                $emailInstance->setRecipientAddress($receiver['email']);
                $this->emailRepository->update($emailInstance);
                $this->emailRepository->persistAll();
                ++$emailSpooledCount;
            }
        }
        self::getLogger(__CLASS__)->info(
            "Queued $emailSpooledCount emails to be sent for newsletter " . $newsletter->getUid()
        );

        // Schedule repeated newsletter if any
        $this->newsletterService->scheduleNextNewsletter($newsletter);

        // Unlock the newsletter by setting its end_time
        $newsletter->setEndTime(new DateTime());
        $this->newsletterRepository->update($newsletter);
        $this->emailRepository->persistAll();
    }

    /**
     * Run the spool for all Newsletters, with a security to avoid parallel sending
     */
    public function runAllSpool()
    {
        $rs = Tools::executeRawDBQuery(
            'SELECT COUNT(uid) FROM tx_newsletter_domain_model_email WHERE begin_time > ' . (time() - 30)
        )->fetchOne();
        if ($rs !== 0) {
            return;
        }

        $this->runSpool();
    }

    /**
     * Run the spool for one or all Newsletters
     *
     * @param Newsletter $limitNewsletter if specified, run spool only for that Newsletter
     */
    public function runSpool(Newsletter $limitNewsletter = null)
    {
        $emailSentCount = 0;
        $mailers = [];

        $allUids = $this->newsletterRepository->findAllNewsletterAndEmailUidToSend($limitNewsletter);
        $this->logger->info('Will send ' . count($allUids) . ' emails');

        $oldNewsletterUid = null;
        foreach ($allUids as $uids) {
            $this->logger->debug("Will send newsletter #" . $uids['newsletter']);
            $newsletterUid = $uids['newsletter'];
            $emailUid = $uids['email'];

            /* For the page, this way we can support multiple pages in one spool session */
            if ($newsletterUid != $oldNewsletterUid) {
                $oldNewsletterUid = $newsletterUid;
                $mailers = [];

                /** @var Newsletter $newsletter */
                $newsletter = $this->newsletterRepository->findByUid($newsletterUid);
            }

            // Define the language of email
            /** @var Email $email */
            $email = $this->emailRepository->findByUid($emailUid);
            $recipientData = $email->getRecipientData();
            $this->logger->debug(
                "Newsletter recipient data for email " . $recipientData['email'] ?? '',
                $recipientData
            );

            $language = $recipientData['L'] ?? '';

            // Was a language with this page defined, if not create one
            if (!array_key_exists($language, $mailers) || !is_object($mailers[$language])) {
                $mailers[$language] = self::getConfiguredMailer($newsletter, $language);
            }

            // Mark it as started sending
            $email->setBeginTime(new DateTime());
            $this->emailRepository->update($email);
            $this->emailRepository->persistAll();
            // Send the email
            $mailers[$language]->send($email);

            // Mark it as sent already
            $email->setEndTime(new DateTime());
            $this->emailRepository->update($email);
            $this->emailRepository->persistAll();
            ++$emailSentCount;
            $this->logger->debug("Sent $emailSentCount emails will wait 2 secs");
            sleep(2);
        }

        // Log numbers
        $this->logger->info("Sent $emailSentCount emails");
    }

    /**
     * Returns an base64_encode encrypted string
     *
     * @param string $data
     *
     * @return string base64_encode encrypted string
     */
    public static function encrypt($data)
    {
        $iv = openssl_random_pseudo_bytes(self::getInitializationVectorSize());

        $encryptedData = openssl_encrypt($data, self::$OPEN_SSL_CIPHER, self::getSecureKey(), 0, $iv);

        return base64_encode($iv . $encryptedData);
    }

    /**
     * Returns a decrypted string
     *
     * @param string $string base64_encode encrypted string
     *
     * @return string decrypted string
     */
    public static function decrypt($string)
    {
        $string = base64_decode($string, true);
        $ivSize = self::getInitializationVectorSize();
        $iv = substr($string, 0, $ivSize);
        $encryptedData = substr($string, $ivSize);

        return trim(openssl_decrypt($encryptedData, self::$OPEN_SSL_CIPHER, self::getSecureKey(), 0, $iv));
    }

    /**
     * Returns the size of the initialization vector
     *
     * @return int
     */
    private static function getInitializationVectorSize()
    {
        return openssl_cipher_iv_length(self::$OPEN_SSL_CIPHER);
    }

    /**
     * Returns the secure encryption key
     *
     * @return string
     */
    private static function getSecureKey()
    {
        return hash('sha256', $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'], true);
    }

    /**
     * Return the full user agent string to be used in HTTP headers
     *
     * @return string
     */
    public static function getUserAgent()
    {
        $userAgent = $GLOBALS['TYPO3_CONF_VARS']['HTTP']['headers']['User-Agent'] . ' Newsletter (https://github.com/Mirko/newsletter)';

        return $userAgent;
    }

    /**
     * Fetch and returns the content at specified URL
     *
     * @param string $url
     *
     * @return string
     */
    public static function getUrl($url, $includeHeader = 0, $requestHeaders = null, &$report = null)
    {
        // Specify User-Agent header if we fetch an URL, but not if it's a file on disk
        if (Utility\Uri::isAbsolute($url)) {
            $requestHeaders = [self::getUserAgent()];
        }

        if (isset($report)) {
            $report['error'] = 0;
            $report['message'] = '';
        }
        // Looks like it's an external file, use Guzzle by default
        if (preg_match('/^(?:http|ftp)s?|s(?:ftp|cp):/', $url)) {
            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            if (is_array($requestHeaders)) {
                $configuration = ['headers' => $requestHeaders];
            } else {
                $configuration = [];
            }
            $includeHeader = (int)$includeHeader;
            $method = $includeHeader === 2 ? 'HEAD' : 'GET';
            try {
                if (isset($report)) {
                    $report['lib'] = 'GuzzleHttp';
                }
                $response = $requestFactory->request($url, $method, $configuration);
            } catch (RequestException $exception) {
                if (isset($report)) {
                    $report['error'] = $exception->getCode() ?: 1518707554;
                    $report['message'] = $exception->getMessage();
                    $report['exception'] = $exception;
                }

                return false;
            }
            $content = '';
            // Add the headers to the output
            if ($includeHeader) {
                $parsedURL = parse_url($url);
                $content = $method . ' ' . ($parsedURL['path'] ?? '/')
                    . (!empty($parsedURL['query']) ? '?' . $parsedURL['query'] : '') . ' HTTP/1.0' . CRLF
                    . 'Host: ' . $parsedURL['host'] . CRLF
                    . 'Connection: close' . CRLF;
                if (is_array($requestHeaders)) {
                    $content .= implode(CRLF, $requestHeaders) . CRLF;
                }
                foreach ($response->getHeaders() as $headerName => $headerValues) {
                    $content .= $headerName . ': ' . implode(', ', $headerValues) . CRLF;
                }
                // Headers are separated from the body with two CRLFs
                $content .= CRLF;
            }

            $content .= $response->getBody()->getContents();

            if (isset($report)) {
                if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 400) {
                    $report['http_code'] = $response->getStatusCode();
                    $report['content_type'] = $response->getHeaderLine('Content-Type');
                    $report['error'] = $response->getStatusCode();
                    $report['message'] = $response->getReasonPhrase();
                } elseif (empty($content)) {
                    $report['error'] = $response->getStatusCode();
                    $report['message'] = $response->getReasonPhrase();
                } elseif ($includeHeader) {
                    // Set only for $includeHeader to work exactly like PHP variant
                    $report['http_code'] = $response->getStatusCode();
                    $report['content_type'] = $response->getHeaderLine('Content-Type');
                }
            }
        } else {
            if (isset($report)) {
                $report['lib'] = 'file';
            }
            $content = @file_get_contents($url);
            if ($content === false && isset($report)) {
                $report['error'] = -1;
                $report['message'] = 'Couldn\'t get URL: ' . $url;
            }
        }

        // Throw Exception if content could not be fetched so that it is properly caught in Validator
        if ($content === false) {
            throw new \Exception(
                'Could not fetch "' . $url . '"' . PHP_EOL . 'Error: ' . $report['error'] . PHP_EOL . 'Message: ' . $report['message']
            );
        }

        return $content;
    }

    public static function getBaseUrl($pid = null)
    {
        // Is anything hardcoded from TYPO3_CONF_VARS ?
        $domain = Tools::confParam('fetch_path');

        // Else we try to resolve a domain in page root line

        // Else we try to find it in sys_template (available at least since TYPO3 4.6 Introduction Package)
        if (!$domain && $pid) {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

            $domain = $siteFinder->getSiteByPageId($pid)->getBase()->getHost();
        }

        if (!$domain) {
            $domain = $_SERVER['HTTP_HOST'];
        }

        // If still no domain, can't continue
        if (!$domain) {
            throw new \Exception(
                "Could not find the domain name. Use Newsletter configuration page to set 'fetch_path'"
            );
        }

        // Force scheme if found from domain record, or if fetch_path was not configured properly (before Newsletter 2.6.0)
        if (!preg_match('~^https?://~', $domain)) {
            $domain = 'http://' . $domain;
        }

        return $domain;
    }

    /**
     * Get domain name
     *
     * @return string domain, eg: www.example.com
     */
    public static function getDomain(): string
    {
        return parse_url(static::getBaseUrl(), PHP_URL_HOST);
    }

    /**
     * @param $tableName
     * @return QueryBuilder
     */
    public static function getQueryBuilderForTable($tableName): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($tableName)
            ->createQueryBuilder();
    }

    /**
     * @param string $sql
     * @return Result|void
     * @throws Exception
     */
    public static function executeRawDBQuery(string $sql)
    {
        try {
            $databaseConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName(
                ConnectionPool::DEFAULT_CONNECTION_NAME
            );

            return $databaseConnection->prepare($sql)->executeQuery();
        } catch (\Exception $exception) {
        }
    }

    /**
     * @return NewsletterRepository
     */
    public function getNewsletterRepository(): NewsletterRepository
    {
        return $this->newsletterRepository;
    }
}
