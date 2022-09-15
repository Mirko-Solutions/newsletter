<?php

namespace Mirko\Newsletter\ViewHelpers;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * View helper which allows you to include a CSS File.
 * Note: This feature is experimental!
 * Note: You MUST wrap this Helper with <newsletter:Be.moduleContainer>-Tags
 *
 * = Examples =
 *
 * <newsletter:be.moduleContainer pageTitle="foo">
 *    <newsletter:includeCssFile name="foo.js" extKey="blog_example" pathInsideExt="Resources/Public/JavaScript" />
 * </newsletter:be.moduleContainer>
 */
class IncludeCssFileViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('name', 'string', 'Name', false, null);
        $this->registerArgument('extKey', 'string', 'Extension key', false, null);
        $this->registerArgument(
            'pathInsideExt',
            'string',
            'Path inside extension',
            false,
            'Resources/Public/Styles/'
        );
    }

    /**
     * Calls addCssFile on the Instance of TYPO3\CMS\Core\Page\PageRenderer.
     *
     * @return string the link
     */
    public function render()
    {
        $name = $this->arguments['name'];
        $extKey = $this->arguments['extKey'];
        $pathInsideExt = $this->arguments['pathInsideExt'];

        if ($extKey === null) {
            $extKey = $this->renderingContext->getRequest()->getControllerExtensionKey();
        }

        $extPath = ExtensionManagementUtility::extPath($extKey);
        $extRelPath = '/' . mb_substr($extPath, mb_strlen(Environment::getPublicPath() . '/'));

        $this->pageRenderer->addCssFile($extRelPath . $pathInsideExt . $name);
    }
}
