<?php

namespace Mirko\Newsletter\Controller;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use Mirko\Newsletter\MVC\Controller\ApiActionController;
use Mirko\Newsletter\Domain\Repository\BounceAccountRepository;

/**
 * Controller for the BounceAccount object
 */
class BounceAccountController extends ApiActionController
{
    /**
     * bounceAccountRepository
     *
     * @var BounceAccountRepository
     */
    protected BounceAccountRepository $bounceAccountRepository;

    /**
     * injectBounceAccountRepository
     *
     * @param BounceAccountRepository $bounceAccountRepository
     */
    public function injectBounceAccountRepository(BounceAccountRepository $bounceAccountRepository)
    {
        $this->bounceAccountRepository = $bounceAccountRepository;
    }

    /**
     * Displays all BounceAccounts
     *
     * return The rendered list view
     */
    public function listAction()
    {
        $bounceAccounts = $this->bounceAccountRepository->findAll();

        $this->view->setVariablesToRender(['total', 'data', 'success', 'flashMessages']);
        $this->view->setConfiguration(
            [
                'data' => [
                    '_descendAll' => self::resolveJsonViewConfiguration(),
                ],
            ]
        );

        $this->addFlashMessage(
            'Loaded BounceAccounts from Server side.',
            'BounceAccounts loaded successfully',
            FlashMessage::NOTICE
        );

        $this->view->assign('total', $bounceAccounts->count());
        $this->view->assign('data', $bounceAccounts);
        $this->view->assign('success', true);
        $this->flushFlashMessages();
    }

    /**
     * Returns a configuration for the JsonView, that describes which fields should be rendered for
     * a BounceAccount record.
     *
     * @return array
     */
    public static function resolveJsonViewConfiguration(): array
    {
        return [
            '_exposeObjectIdentifier' => true,
            '_only' => [
                'email',
                'server',
                'protocol',
                'username',
            ],
        ];
    }
}
