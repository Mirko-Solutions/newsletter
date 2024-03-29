<?php

namespace Mirko\Newsletter\Controller;

use Mirko\Newsletter\Domain\Repository\RecipientListRepository;
use Mirko\Newsletter\Helper\Typo3CompatibilityHelper;
use Mirko\Newsletter\Service\Typo3GeneralService;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Mail\MailMessage;
use Mirko\Newsletter\Domain\Model\Email;
use Mirko\Newsletter\Utility\EmailParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Mirko\Newsletter\Domain\Model\Newsletter;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use Mirko\Newsletter\Domain\Model\RecipientList;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Mirko\Newsletter\Domain\Repository\EmailRepository;
use Mirko\Newsletter\MVC\Controller\ApiActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * Controller for the Email object
 */
class EmailController extends ApiActionController
{
    /**
     * emailRepository
     *
     * @var EmailRepository
     */
    protected EmailRepository $emailRepository;

    /**
     * recipientListRepository
     *
     * @var RecipientListRepository
     */
    protected RecipientListRepository $recipientListRepository;

    /**
     * injectEmailRepository
     *
     * @param EmailRepository $emailRepository
     */
    public function injectEmailRepository(EmailRepository $emailRepository)
    {
        $this->emailRepository = $emailRepository;
    }

    /**
     * injectEmailRepository
     *
     * @param RecipientListRepository $recipientListRepository
     */
    public function injectRecipientListRepository(RecipientListRepository $recipientListRepository)
    {
        $this->recipientListRepository = $recipientListRepository;
    }

    /**
     * Displays all Emails
     *
     * @param int $uidNewsletter
     * @param int $start
     * @param int $limit
     *
     * return The rendered list view
     */
    public function listAction(int $uidNewsletter, int $start, int $limit)
    {
        $emails = $this->emailRepository->findAllByNewsletter($uidNewsletter, $start, $limit);

        $this->view->setVariablesToRender(['total', 'data', 'success', 'flashMessages']);
        $this->view->setConfiguration(
            [
                'data' => [
                    '_descendAll' => self::resolveJsonViewConfiguration(),
                ],
            ]
        );

        $this->addFlashMessage(
            'Loaded all Emails from Server side.',
            'Emails loaded successfully',
            AbstractMessage::NOTICE
        );
        $this->view->assign('total', $this->emailRepository->getCount($uidNewsletter));
        $this->view->assign('data', $emails);
        $this->view->assign('success', true);
        if (Typo3CompatibilityHelper::typo3VersionIs10()) {
            $this->view->assign(
                'flashMessages',
                $this->controllerContext->getFlashMessageQueue()->getAllMessagesAndFlush()
            );
        } else {
            $this->view->assign(
                'flashMessages',
                $this->getFlashMessageQueue()->getAllMessagesAndFlush()
            );
        }
    }

    /**
     * Register when an email was opened
     * For this method we don't use extbase parameters system to have an URL as short as possible
     */
    public function openedAction()
    {
        $args = $this->request->getArguments();
        $this->emailRepository->registerOpen(@$args['c']);

        // Send one transparent pixel, so the end-user sees nothing at all
        header('Content-type: image/gif');
        readfile(ExtensionManagementUtility::extPath('newsletter', '/Resources/Private/clear.gif'));
        die();
    }

    /**
     * Show a preview or the final version of an email
     * For this method we don't use extbase parameters system to have an URL as short as possible
     */
    public function showAction()
    {
        // Override settings to NOT embed images inlines (doesn't make sense for web display)
        global $TYPO3_CONF_VARS;
        $theConf = Typo3GeneralService::getExtensionConfiguration();
        $theConf['attach_images'] = false;
        $TYPO3_CONF_VARS['EXTENSIONS']['newsletter'] = $theConf;

        $newsletter = null;
        $email = null;
        $args = $this->request->getArguments();

        // For compatibility with old links
        $otherArgs = [
            'type',
            'uidRecipientList',
            'c',
            'pid',
            'plainConverter',
            'injectOpenSpy',
            'injectLinksSpy',
            'email',
            'plain',
            'L',
        ];
        foreach ($otherArgs as $arg) {
            if (!isset($args[$arg]) && isset($_GET[$arg])) {
                $args[$arg] = $_GET[$arg];
            }
        }

        $isPreview = empty($args['c']); // If we don't have an authentification code, we are in preview mode
        // If it's a preview, an email which was not sent yet, we will simulate it the best we can
        if ($isPreview) {
            // Create a fake newsletter and configure it with given parameters
            /** @var Newsletter $newsletter */
            $newsletter = GeneralUtility::makeInstance(Newsletter::class);
            $newsletter->setPid(@$args['pid']);
            /**
             * @var RecipientList $recipientList
             */
            $recipientList = $this->recipientListRepository->findByUid(@$args['uidRecipientList']);
            if ($recipientList instanceof RecipientList) {
                $newsletter->setRecipientList($recipientList);
                // Find the recipient
                $recipientList = $newsletter->getRecipientList();
                $recipientList->init();
                while ($record = $recipientList->getRecipient()) {
                    // Got him
                    if ($record['email'] === $args['email']) {
                        // Build a fake email
                        $email = GeneralUtility::makeInstance(Email::class);
                        $email->setRecipientAddress($record['email']);
                        $email->setRecipientData($record);
                    }
                }
            }
        } else {
            // Otherwise look for the original email which was already sent
            $email = $this->emailRepository->findByAuthcode($args['c']);
            if ($email) {
                $newsletter = $email->getNewsletter();

                // Here we need to ensure that we have real newsletter instance because of type hinting on \Mirko\Newsletter\Tools::getConfiguredMailer()
                if ($newsletter instanceof LazyLoadingProxy) {
                    $newsletter = $newsletter->_loadRealInstance();
                }
            }
        }

        // If we found everything needed, we can render the email
        $content = null;
        if ($newsletter && $email) {
            // Override some configuration
            // so we can customise the preview according to selected settings via JS,
            // and we can also prevent fake statistics when admin 'view' a sent email
            if (isset($args['plainConverter'])) {
                $newsletter->setPlainConverter($args['plainConverter']);
            }

            if (isset($args['injectOpenSpy'])) {
                $newsletter->setInjectOpenSpy($args['injectOpenSpy']);
            }

            if (isset($args['injectLinksSpy'])) {
                $newsletter->setInjectLinksSpy($args['injectLinksSpy']);
            }

            $mailer = Tools::getConfiguredMailer($newsletter, @$args['L']);
            $mailer->prepare($email, $isPreview);

            if (@$args['plain']) {
                $content = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body><pre>';
                $content .= $mailer->getPlain();
                $content .= '</pre></body></html>';
            } else {
                $content = $mailer->getHtml();
            }
        }

        $this->view->assign('content', $content);
    }

    /**
     * Unsubscribe recipient from RecipientList by registering a bounce of level \Mirko\Newsletter\Utility\EmailParser::NEWSLETTER_UNSUBSCRIBE
     */
    public function unsubscribeAction()
    {
        $success = false;
        $recipientAddress = null;

        $args = $this->request->getArguments();

        // For compatibility with old links
        if (!isset($args['c']) && isset($_GET['c'])) {
            $args['c'] = $_GET['c'];
        }

        // If we have an authentification code, look for the original email which was already sent
        if (@$args['c']) {
            $email = $this->emailRepository->findByAuthcode($args['c']);
            if ($email) {
                // Mark the email as requested to be unsubscribed
                $email->setUnsubscribed(true);
                $this->emailRepository->update($email);
                $this->emailRepository->persistAll();
                $recipientAddress = $email->getRecipientAddress();

                $newsletter = $email->getNewsletter();
                if ($newsletter) {
                    $recipientList = $newsletter->getRecipientList();
                    $recipientList->registerBounce($email->getRecipientAddress(), EmailParser::NEWSLETTER_UNSUBSCRIBE);
                    $success = true;
                    $this->notifyUnsubscribe($newsletter, $recipientList, $email);
                }
            }
        }

        // Redirect unsubscribe via config.
        $redirect = Tools::confParam('unsubscribe_redirect');

        // If it is a PID, convert to a URL
        if (is_numeric($redirect)) {
            $uriBuilder = $this->controllerContext->getUriBuilder();
            $uriBuilder->reset();
            $uriBuilder->setTargetPageUid((int)$redirect);
            // Append the recipient address just in case you want to do something with it at the destination
            $uriBuilder->setArguments(
                [
                    'recipient' => $recipientAddress,
                ]
            );
            $uriBuilder->setCreateAbsoluteUri(true);
            $redirect = $uriBuilder->build();
        }

        // If it is a valid URL, redirect to it
        if (GeneralUtility::isValidUrl($redirect)) {
            $this->redirectToUri($redirect);
        }

        // Else render the template.
        $this->view->assign('success', $success);
        $this->view->assign('recipientAddress', $recipientAddress);
    }

    /**
     * Sends an email to the address configured in extension settings when a recipient unsubscribe
     *
     * @param Newsletter $newsletter
     * @param RecipientList $recipientList
     * @param Email $email
     */
    protected function notifyUnsubscribe($newsletter, $recipientList, Email $email)
    {
        $notificationEmail = Tools::confParam('notification_email');

        // Use the page-owner as user
        if ($notificationEmail == 'user') {
            $notificationEmail = Tools::executeRawDBQuery(
                'SELECT email
			FROM be_users
			LEFT JOIN pages ON be_users.uid = pages.perms_userid
			WHERE pages.uid = ' . $newsletter->getPid()
            )->fetchOne();
        }

        // If cannot find valid email, don't send any notification
        if (!GeneralUtility::validEmail($notificationEmail)) {
            return;
        }

        // Build email texts
        $baseUrl = Tools::getBaseUrl();
        $urlRecipient = $baseUrl . '/typo3/alt_doc.php?&edit[tx_newsletter_domain_model_email][' . $email->getUid(
            ) . ']=edit';
        $urlRecipientList = $baseUrl . '/typo3/alt_doc.php?&edit[tx_newsletter_domain_model_recipientlist][' . $recipientList->getUid(
            ) . ']=edit';
        $urlNewsletter = $baseUrl . '/typo3/alt_doc.php?&edit[tx_newsletter_domain_model_newsletter][' . $newsletter->getUid(
            ) . ']=edit';
        $subject = LocalizationUtility::translate('unsubscribe_notification_subject', 'newsletter');
        $body = LocalizationUtility::translate(
            'unsubscribe_notification_body',
            'newsletter',
            [$email->getRecipientAddress(), $urlRecipient, $recipientList->getTitle(
            ), $urlRecipientList, $newsletter->getTitle(), $urlNewsletter]
        );

        // Actually sends email
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->setTo($notificationEmail)
            ->setFrom([$newsletter->getSenderEmail() => $newsletter->getSenderName()])
            ->setSubject($subject)
            ->html($body);
        $message->send();
    }

    /**
     * Returns a configuration for the JsonView, that describes which fields should be rendered for
     * a Email record.
     *
     * @return array
     */
    public static function resolveJsonViewConfiguration()
    {
        return [
            '_exposeObjectIdentifier' => true,
            '_only' => ['beginTime', 'endTime', 'authCode', 'bounceTime', 'openTime', 'recipientAddress', 'unsubscribed'],
            '_descend' => [
                'beginTime' => [],
                'endTime' => [],
                'openTime' => [],
                'bounceTime' => [],
            ],
        ];
    }
}
