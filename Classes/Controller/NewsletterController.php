<?php

namespace Mirko\Newsletter\Controller;

use DateTime;
use Mirko\Newsletter\Service\NewsletterService;
use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use Mirko\Newsletter\Domain\Model\Newsletter;
use Mirko\Newsletter\MVC\Controller\ApiActionController;
use Mirko\Newsletter\Domain\Repository\NewsletterRepository;
use Mirko\Newsletter\Domain\Repository\BounceAccountRepository;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;

/**
 * Controller for the Newsletter object
 */
class NewsletterController extends ApiActionController
{
    /**
     * newsletterRepository
     *
     * @var NewsletterRepository
     */
    protected NewsletterRepository $newsletterRepository;

    /**
     * bounceAccountRepository
     *
     * @var BounceAccountRepository
     */
    protected BounceAccountRepository $bounceAccountRepository;

    /**
     * @var NewsletterService
     */
    private NewsletterService $newsletterService;

    /**
     * injectNewsletterRepository
     *
     * @param NewsletterRepository $newsletterRepository
     */
    public function injectNewsletterRepository(NewsletterRepository $newsletterRepository)
    {
        $this->newsletterRepository = $newsletterRepository;
    }

    /**
     * injectBounceAccounRepository
     *
     * @param BounceAccountRepository $bounceAccountRepository
     */
    public function injectBounceAccountRepository(BounceAccountRepository $bounceAccountRepository)
    {
        $this->bounceAccountRepository = $bounceAccountRepository;
    }

    /**
     * injectNewsletterService
     *
     * @param NewsletterService $newsletterService
     */
    public function injectNewsletterService(NewsletterService $newsletterService)
    {
        $this->newsletterService = $newsletterService;
    }

    /**
     * Parent id
     *
     * @var int
     */
    protected int $pid = 0;

    /**
     * Initializes the current action
     */
    protected function initializeAction()
    {
        // Set default value of PID to know where to store/look for newsletter
        $this->pid = filter_var(GeneralUtility::_GET('id'), FILTER_VALIDATE_INT, ['min_range' => 0]);
        if (!$this->pid) {
            $this->pid = 0;
        }
        parent::initializeAction();
    }

    /**
     * Displays all Newsletters
     *
     * @return string The rendered list view
     */
    public function listAction()
    {
        $newsletters = $this->newsletterRepository->findAllByPid($this->pid);

        $this->view->setVariablesToRender(['total', 'data', 'success', 'flashMessages']);
        $this->view->setConfiguration(
            [
                'data' => [
                    '_descendAll' => self::resolveJsonViewConfiguration(),
                ],
            ]
        );

        $this->addFlashMessage(
            'Loaded Newsletters from Server side.',
            'Newsletters loaded successfully',
            AbstractMessage::NOTICE
        );

        $this->view->assign('total', $newsletters->count());
        $this->view->assign('data', $newsletters);
        $this->view->assign('success', true);
        $this->flushFlashMessages();
    }

    /**
     * Displays the newsletter used as model for plannification
     *
     * @return string The rendered list view
     */
    public function listPlannedAction()
    {
        $newsletter = $this->newsletterRepository->getLatest($this->pid);
        if (!$newsletter) {
            $newsletter = GeneralUtility::makeInstance(Newsletter::class);
            $newsletter->setPid($this->pid);
            $newsletter->setUid(-1);
            // Set the first Bounce Account found if any
            $newsletter->setBounceAccount($this->bounceAccountRepository->findFirst());
        }

        // Default planned time is right now
        $newsletter->setPlannedTime(new DateTime());

        $this->view->setVariablesToRender(['total', 'data', 'success']);
        $this->view->setConfiguration(
            [
                'data' => self::resolvePlannedJsonViewConfiguration(),
            ]
        );

        $this->view->assign('total', 1);
        $this->view->assign('data', $newsletter);
        $this->view->assign('success', true);
        $this->flushFlashMessages();
    }

    /**
     * Allow 'pid' to be mapped
     */
    protected function initializeCreateAction()
    {
        $propertyMappingConfiguration = $this->arguments['newNewsletter']->getPropertyMappingConfiguration();
        $propertyMappingConfiguration->allowAllProperties();
        $propertyMappingConfiguration->setTypeConverterOption(
            PersistentObjectConverter::class,
            PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
            true
        );
    }

    /**
     * Creates a new Newsletter and forwards to the list action.
     *
     * @param Newsletter $newNewsletter a fresh Newsletter object which has not yet been added to the repository
     *
     * @throws IllegalObjectTypeException
     * @Extbase\IgnoreValidation("newNewsletter")
     */
    public function createAction(Newsletter $newNewsletter)
    {
        $limitTestRecipientCount = 10; // This is a low limit, technically, but it does not make sense to test a newsletter for more people than that anyway
        $recipientList = $newNewsletter->getRecipientList();
        $recipientList->init();
        $count = $recipientList->getCount();
        $validatedContent = $newNewsletter->getValidatedContent();

        // If we attempt to create a newsletter as a test but it has too many recipient, reject it (we cannot safely send several emails wihtout slowing down respoonse and/or timeout issues)
        if ($newNewsletter->getIsTest() && $count > $limitTestRecipientCount) {
            $this->addFlashMessage(
                $this->translate('flashmessage_test_maximum_recipients', [$count, $limitTestRecipientCount]),
                $this->translate('flashmessage_test_maximum_recipients_title'),
                AbstractMessage::ERROR
            );
            $this->view->assign('success', false);
        } // If we attempt to create a newsletter which contains errors, abort and don't save in DB
        elseif (count($validatedContent['errors'])) {
            $this->addFlashMessage(
                'The newsletter HTML content does not validate. See tab "Newsletter > Status" for details.',
                $this->translate('flashmessage_newsletter_invalid'),
                AbstractMessage::ERROR
            );
            $this->view->assign('success', false);
        } else {
            // If it's a test newsletter, it's planned to be sent right now
            if ($newNewsletter->getIsTest()) {
                $newNewsletter->setPlannedTime(new DateTime());
            }

            // Save the new newsletter
            $this->newsletterRepository->add($newNewsletter);
            $this->persistenceManager->persistAll();
            $this->view->assign('success', true);
            // If it is test newsletter, send it immediately
            if ($newNewsletter->getIsTest()) {
                try {
                    // Fill the spool and run the queue
                    $tools = Tools::getInstance();
                    $tools->createSpool($newNewsletter);
                    $tools->runSpool($newNewsletter);

                    $this->addFlashMessage(
                        $this->translate('flashmessage_test_newsletter_sent'),
                        $this->translate('flashmessage_test_newsletter_sent_title')
                    );
                } catch (\Exception $exception) {
                    $this->addFlashMessage(
                        $exception->getMessage(),
                        $this->translate('flashmessage_test_newsletter_error'),
                        AbstractMessage::ERROR
                    );
                }
            } else {
                $this->addFlashMessage(
                    $this->translate('flashmessage_newsletter_queued'),
                    $this->translate('flashmessage_newsletter_queued_title')
                );
            }
        }

        $this->view->setVariablesToRender(['data', 'success', 'flashMessages']);
        $this->view->setConfiguration(
            [
                'data' => self::resolveJsonViewConfiguration(),
            ]
        );

        $this->view->assign('data', $newNewsletter);
        $this->flushFlashMessages();
    }

    /**
     * Returns the newsletter with included statistics to be used for timeline chart
     *
     * @param int $uidNewsletter
     */
    public function statisticsAction(int $uidNewsletter)
    {
        /** @var Newsletter $newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByUid($uidNewsletter);

        $this->view->setVariablesToRender(['data', 'success', 'total']);

        $conf = self::resolveJsonViewConfiguration();
        $conf['_only'][] = 'statistics';
        $conf['_descend'][] = 'statistics';
        $this->view->setConfiguration(
            [
                'data' => $conf,
            ]
        );

        $this->view->assign('total', 1);
        $this->view->assign('success', true);
        $this->view->assign('data', $newsletter);
    }

    /**
     * Returns a configuration for the JsonView, that describes which fields should be rendered for
     * a Newsletter record.
     *
     * @return array
     */
    public static function resolveJsonViewConfiguration(): array
    {
        return [
            '_exposeObjectIdentifier' => true,
            '_only' => [
                'pid',
                'beginTime',
                'bounceAccount',
                'endTime',
                'injectLinksSpy',
                'injectOpenSpy',
                'isTest',
                'plainConverter',
                'plannedTime',
                'repetition',
                'senderEmail',
                'senderName',
                'replytoEmail',
                'replytoName',
                'title',
                'emailCount',
            ],
            '_descend' => [
                'beginTime' => [],
                'endTime' => [],
                'plannedTime' => [],
                'statistics' => [],
            ],
        ];
    }

    public static function resolvePlannedJsonViewConfiguration(): array
    {
        return [
            '_exposeObjectIdentifier' => true,
            '_only' => [
                'pid',
                'beginTime',
                'uidBounceAccount',
                'uidRecipientList',
                'endTime',
                'injectLinksSpy',
                'injectOpenSpy',
                'isTest',
                'plainConverter',
                'plannedTime',
                'repetition',
                'senderEmail',
                'senderName',
                'replytoEmail',
                'replytoName',
                'title',
                'validatedContent',
                'status',
            ],
            '_descend' => [
                'beginTime' => [],
                'endTime' => [],
                'plannedTime' => [],
                'validatedContent' => [
                    '_only' => [
                        'errors',
                        'warnings',
                        'infos',
                    ],
                ],
            ],
        ];
    }
}
