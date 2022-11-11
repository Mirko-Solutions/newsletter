<?php

namespace Mirko\Newsletter\Controller;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Error\Http\PageNotFoundException;
use Mirko\Newsletter\Domain\Repository\LinkRepository;
use Mirko\Newsletter\MVC\Controller\ApiActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

/**
 * Controller for the Link object
 */
class LinkController extends ApiActionController
{
    /**
     * linkRepository
     *
     * @var LinkRepository
     */
    protected LinkRepository $linkRepository;

    /**
     * injectLinkRepository
     *
     * @param LinkRepository $linkRepository
     */
    public function injectLinkRepository(LinkRepository $linkRepository)
    {
        $this->linkRepository = $linkRepository;
    }

    /**
     * Displays all Links
     *
     * @param int $uidNewsletter
     * @param int $start
     * @param int $limit
     */
    public function listAction($uidNewsletter, $start, $limit): void
    {
        $links = $this->linkRepository->findAllByNewsletter($uidNewsletter, $start, $limit);

        $this->view->setVariablesToRender(['total', 'data', 'success', 'flashMessages']);
        $this->view->setConfiguration(
            [
                'data' => [
                    '_descendAll' => self::resolveJsonViewConfiguration(),
                ],
            ]
        );

        $this->addFlashMessage(
            'Loaded all Links from Server side.',
            'Links loaded successfully',
            AbstractMessage::NOTICE
        );

        $this->view->assign('total', $this->linkRepository->getCount($uidNewsletter));
        $this->view->assign('data', $links);
        $this->view->assign('success', true);
        $this->view->assign(
            'flashMessages',
            $this->getFlashMessageQueue()->getAllMessagesAndFlush()
        );
    }

    /**
     * Register when a link was clicked and redirect to link's URL.
     * For this method we don't use Extbase parameters system to have an URL as short as possible
     * @throws PageNotFoundException|StopActionException
     */
    public function clickedAction()
    {
        $args = $this->request->getArguments();

        // For compatibility with old links
        $oldArgs = ['n', 'l', 'p'];
        foreach ($oldArgs as $arg) {
            if (!isset($args[$arg]) && isset($_REQUEST[$arg])) {
                $args[$arg] = $_REQUEST[$arg];
            }
        }

        $url = $this->linkRepository->registerClick(@$args['n'], @$args['l'], @$args['p']);

        // Finally redirect to the destination URL
        if ($url) {
            // This gives a proper 303 redirect.
            $this->redirectToUri($url);
        } else {
            throw new PageNotFoundException('The requested link was not found', 1440490767);
        }
    }

    /**
     * Returns a configuration for the JsonView, that describes which fields should be rendered for
     * a Link record.
     *
     * @return array
     */
    public static function resolveJsonViewConfiguration(): array
    {
        return [
            '_exposeObjectIdentifier' => true,
            '_only' => ['url', 'openedCount', 'openedPercentage'],
        ];
    }
}
