<?php

namespace Mirko\Newsletter\ViewHelpers;

use Mirko\Newsletter\Exception;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * View helper which allows you to include inline JS code into a module Container.
 * Note: This feature is experimental!
 * Note: You MUST wrap this Helper with <newsletter:Be.moduleContainer>-Tags
 *
 * = Examples =
 *
 * <newsletter:be.moduleContainer pageTitle="foo">
 *    <newsletter:includeExtOnReadyCode file="foo.js" extKey="blog_example" pathInsideExt="Resources/Public/JavaScript" />
 * </newsletter:be.moduleContainer>
 */
class IncludeExtOnReadyFromFileViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('name', 'string', 'Name', false, 'extOnReady.js');
        $this->registerArgument('extKey', 'string', 'Extension key', false, null);
        $this->registerArgument(
            'pathInsideExt',
            'string',
            'Path inside extension',
            false,
            'Resources/Public/JavaScript/'
        );
    }

    /**
     * Calls addJsFile on the Instance of TYPO3\CMS\Core\Page\PageRenderer.
     *
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

        $filePath = $extPath . $pathInsideExt . $name;

        if (!file_exists($filePath)) {
            throw new Exception('File not found: ' . $filePath, 1264197781);
        }

        $fileContent = file_get_contents($extPath . $pathInsideExt . $name);

        $this->pageRenderer->addJsInlineCode('extOnReady',$fileContent);
    }
}
