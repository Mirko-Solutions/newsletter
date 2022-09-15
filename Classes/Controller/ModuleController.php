<?php

namespace Mirko\Newsletter\Controller;

use Mirko\Newsletter\DataProvider\Backend\NewsletterDataProvider;
use Mirko\Newsletter\Service\Typo3GeneralService;
use Mirko\Newsletter\Tools;
use Mirko\Newsletter\Utility\UriBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * The view based backend module controller for the Newsletter package.
 */
class ModuleController extends ActionController
{
    /**
     * @var int
     */
    protected $pageId;

    /**
     * @var ModuleTemplateFactory
     */
    private ModuleTemplateFactory $moduleTemplateFactory;

    /**
     * @var NewsletterDataProvider
     */
    private NewsletterDataProvider $newsletterDataProvider;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        NewsletterDataProvider $newsletterDataProvider
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->newsletterDataProvider = $newsletterDataProvider;
    }

    /**
     * Initializes the controller before invoking an action method.
     */
    protected function initializeAction()
    {
        $this->pageId = (int)GeneralUtility::_GP('id');
    }

    /**
     * Generates the action menu
     */
    protected function initializeModuleTemplate(ServerRequestInterface $request): ModuleTemplate
    {
        $menuItems = [
            'newsletter' => [
                'controller' => 'Module',
                'action' => 'newsletter',
                'label' => 'Newsletter',
            ],
            'statistics' => [
                'controller' => 'Module',
                'action' => 'statistics',
                'label' => 'Statistics',
            ],
        ];

        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $menu = $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('IndexedSearchModuleMenu');

        $context = '';
        foreach ($menuItems as $menuItemConfig) {
            $isActive = $this->request->getControllerActionName() === $menuItemConfig['action'];
            $menuItem = $menu->makeMenuItem()
                ->setTitle($menuItemConfig['label'])
                ->setHref(
                    $this->uriBuilder->reset()->uriFor($menuItemConfig['action'], [], $menuItemConfig['controller'])
                )
                ->setActive($isActive);
            $menu->addMenuItem($menuItem);
            if ($isActive) {
                $context = $menuItemConfig['label'];
            }
        }

        $moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
        $moduleTemplate->setTitle(
            'Newsletter',
            $context
        );

        $permissionClause = Typo3GeneralService::getBackendUserAuthentication()->getPagePermsClause(Permission::PAGE_SHOW);
        $pageRecord = BackendUtility::readPageAccess($this->pageId, $permissionClause);
        if ($pageRecord) {
            $moduleTemplate->getDocHeaderComponent()->setMetaInformation($pageRecord);
        }
        $moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());

        return $moduleTemplate;
    }

    public function newsletterAction(): ResponseInterface
    {
        $mode = $this->request->hasArgument('mode') ? $this->request->getArgument('mode') : 'status';

        $pageType = '';
        $record = Tools::getDatabaseConnection()->exec_SELECTgetSingleRow('doktype', 'pages', 'uid =' . $this->pageId);
        if (!empty($record['doktype']) && $record['doktype'] == 254) {
            $pageType = 'folder';
        } elseif (!empty($record['doktype'])) {
            $pageType = 'page';
        }

        $configuration = [
            'pageId' => $this->pageId,
            'pageType' => $pageType,
            'emailShowUrl' => UriBuilder::buildFrontendUri($this->pageId, 'Email', 'show'),
        ];

        $this->view->assignMultiple([
            'configuration', $configuration,
            'mode' => $mode,
            'pageData' => $this->newsletterDataProvider->getDataByMode($mode, $this->pageId)
        ]);

        $moduleTemplate = $this->initializeModuleTemplate($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function statisticsAction(): ResponseInterface
    {
        $pageType = '';
        $record = Tools::getDatabaseConnection()->exec_SELECTgetSingleRow('doktype', 'pages', 'uid =' . $this->pageId);
        if (!empty($record['doktype']) && $record['doktype'] == 254) {
            $pageType = 'folder';
        } elseif (!empty($record['doktype'])) {
            $pageType = 'page';
        }

        $configuration = [
            'pageId' => $this->pageId,
            'pageType' => $pageType,
            'emailShowUrl' => UriBuilder::buildFrontendUri($this->pageId, 'Email', 'show'),
        ];

        $this->view->assign('configuration', $configuration);
        $moduleTemplate = $this->initializeModuleTemplate($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }
}
