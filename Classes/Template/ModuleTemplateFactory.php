<?php

declare(strict_types=1);

namespace Mirko\Newsletter\Template;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * A factory class taking care of building ModuleTemplate objects
 */
class ModuleTemplateFactory
{
    protected PageRenderer $pageRenderer;
    protected IconFactory $iconFactory;
    protected FlashMessageService $flashMessageService;

    public function __construct(
        PageRenderer $pageRenderer,
        IconFactory $iconFactory,
        FlashMessageService $flashMessageService
    ) {
        $this->pageRenderer = $pageRenderer;
        $this->iconFactory = $iconFactory;
        $this->flashMessageService = $flashMessageService;
    }

    public function create(ServerRequestInterface $request): ModuleTemplate
    {
        return new ModuleTemplate(
            $this->pageRenderer,
            $this->iconFactory,
            $this->flashMessageService,
            $request
        );
    }
}
