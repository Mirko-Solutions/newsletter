<?php

namespace Mirko\Newsletter\ViewHelpers\Be;

use Mirko\Newsletter\ViewHelpers\AbstractViewHelper;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * View helper which allows you to create ExtBase-based modules in the style of
 * TYPO3 default modules.
 * Note: This feature is experimental!
 *
 * = Examples =
 *
 * <code title="Simple">
 * {namespace newsletter=Mirko\Newsletter\ViewHelpers}
 * <newsletter:be.container>your additional viewHelpers inside</ext:be.container>
 * </code>
 *
 * Output:
 * "your module content" wrapped with propper head & body tags.
 * Default backend CSS styles and JavaScript will be included
 *
 * <code title="All options">
 * {namespace newsletter=Mirko\Newsletter\ViewHelpers}
 * <newsletter:be.moduleContainer pageTitle="foo">your module content</f:be.container>
 * </code>
 */
class ModuleContainerViewHelper extends AbstractViewHelper
{
    /**
     * Don't escape anything because we will render the entire page
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        $this->registerArgument(
            'pageTitle',
            'string',
            'title tag of the module. Not required by default, as BE modules are shown in a frame',
            true
        );
    }

    /**
     * Renders start page with template.php and pageTitle.
     *
     *
     * @return string
     * @see template
     * @see \TYPO3\CMS\Core\Page\PageRenderer
     */
    public function render()
    {
        $pageTitle = $this->arguments['pageTitle'];
        $doc = $this->getModuleTemplate();
        $this->pageRenderer->backPath = '';
        $this->pageRenderer->loadRequireJs();

        $extPath = ExtensionManagementUtility::extPath('newsletter');
        $extRelPath = '/' . mb_substr($extPath, mb_strlen(Environment::getPublicPath() . '/'));
        $this->pageRenderer->addCssFile($extRelPath . 'Resources/Public/Styles/xtheme-t3skin.css');

        $this->renderChildren();

//        $this->pageRenderer->enableCompressJavaScript();
//        $this->pageRenderer->enableCompressCss();
//        $this->pageRenderer->enableConcatenateCss();
//        $this->pageRenderer->enableConcatenateJavascript();

        $this->pageRenderer->disableCompressCss();
        $this->pageRenderer->disableCompressJavascript();
        $this->pageRenderer->disableConcatenateCss();
        $this->pageRenderer->disableConcatenateJavascript();

        $doc->setTitle($pageTitle);
        $doc->setContent($this->pageRenderer->getBodyContent());

        return $doc->renderContent();
    }
}
