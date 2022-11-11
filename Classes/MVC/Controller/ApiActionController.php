<?php

namespace Mirko\Newsletter\MVC\Controller;

use Mirko\Newsletter\MVC\View\JsonView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3Fluid\Fluid\View\ViewInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * A Controller used for answering via AJAX speaking JSON
 */
class ApiActionController extends ActionController
{
    /**
     * @var PersistenceManagerInterface
     */
    protected PersistenceManagerInterface $persistenceManager;

    /**
     * Injects the PersistenceManager.
     *
     * @param PersistenceManagerInterface $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Initializes the View to be a \Mirko\Newsletter\ExtDirect\View\ExtDirectView that renders json without Template Files.
     *
     * @param ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function initializeView($view)
    {
        if ($this->request->getAttribute('jsonRequest')) {
            $this->view = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Mvc\View\JsonView::class);
        }
    }

    /**
     * Override parent method to render error message for ExtJS (in JSON).
     * Also append detail about what property failed to error message.
     */
    protected function errorAction()
    {
        $message = $this->getFlattenedValidationErrorMessage();

        if ($this->view instanceof JsonView) {
            $this->view->setVariablesToRender(['flashMessages', 'error', 'success']);
            $this->view->assign(
                'flashMessages',
                $this->getFlashMessageQueue()->getAllMessagesAndFlush()
            );
            $this->view->assign('error', $message);
            $this->view->assign('success', false);
        }
    }

    /**
     * Translate key
     *
     * @param string $key
     * @param array $args
     *
     * @return string
     */
    protected function translate(string $key, array $args = []): string
    {
        return LocalizationUtility::translate($key, 'newsletter', $args);
    }

    /**
     * Flush flashMessages into view
     */
    protected function flushFlashMessages(): void
    {
        $this->view->assign(
            'flashMessages',
            $this->getFlashMessageQueue()->getAllMessagesAndFlush()
        );
    }
}
