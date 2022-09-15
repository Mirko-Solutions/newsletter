<?php

namespace Mirko\Newsletter\ViewHelpers;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * View helper which allows you to include a JS File.
 * Note: This feature is experimental!
 * Note: You MUST wrap this Helper with <newsletter:Be.moduleContainer>-Tags
 *
 * = Examples =
 *
 * <newsletter:be.moduleContainer pageTitle="foo">
 *    <newsletter:includeJsFile file="foo.js" extKey="blog_example" pathInsideExt="Resources/Public/JavaScript" />
 * </newsletter:be.moduleContainer>
 */
class IncludeJsFolderViewHelper extends AbstractViewHelper
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
            'Resources/Public/JavaScript/'
        );
        $this->registerArgument('recursive', 'boolean', 'Recursive', false, false);
    }

    /**
     * Calls addJsFile for each file in the given folder on the Instance of TYPO3\CMS\Core\Page\PageRenderer.
     *
     * @param bool $recursive
     */
    public function render()
    {
        $name = $this->arguments['name'];
        $extKey = $this->arguments['extKey'];
        $pathInsideExt = $this->arguments['pathInsideExt'];
        $recursive = $this->arguments['recursive'];

        if ($extKey === null) {
            $extKey = $this->renderingContext->getRequest()->getControllerExtensionKey();
        }
        $extPath = ExtensionManagementUtility::extPath($extKey);
        $extRelPath = '/' . mb_substr($extPath, mb_strlen(Environment::getPublicPath() . '/'));
        $absFolderPath = $extPath . $pathInsideExt . $name;
        // $files will include all files relative to $pathInsideExt
        if ($recursive === false) {
            $files = GeneralUtility::getFilesInDir($absFolderPath);
            foreach ($files as $hash => $filename) {
                $files[$hash] = $name . $filename;
            }
        } else {
            $files = GeneralUtility::getAllFilesAndFoldersInPath([], $absFolderPath, '', 0, 99, '\\.svn');
            foreach ($files as $hash => $absPath) {
                $files[$hash] = str_replace($extPath . $pathInsideExt, '', $absPath);
            }
        }

        foreach ($files as $name) {
            $this->pageRenderer->addJsFile($extRelPath . $pathInsideExt . $name);
        }
    }
}
