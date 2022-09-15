<?php

namespace Mirko\Newsletter\ViewHelpers;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Make localization files available in JavaScript
 */
class LocalizationViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('name', 'string', 'Name');
        $this->registerArgument('extKey', 'string', 'Extension key');
        $this->registerArgument(
            'pathInsideExt',
            'string',
            'Path inside extension',
            false,
            'Resources/Private/Language/'
        );
    }

    public function render()
    {
        $name = $this->arguments['name'];
        $extKey = $this->arguments['extKey'];
        $pathInsideExt = $this->arguments['pathInsideExt'];

        $names = explode(',', $name);

        if ($extKey === null) {
            $extKey = $this->renderingContext->getRequest()->getControllerExtensionKey();
        }

        $extPath = ExtensionManagementUtility::extPath($extKey);

        $localizations = [];
        foreach ($names as $name) {
            $filePath = $extPath . $pathInsideExt . $name;
            $localizations = array_merge($localizations, $this->getLocalizations($filePath));
        }

        $localizations = json_encode($localizations);
        $javascript = "Ext.ux.Mirko.Newsletter.Language = $localizations;";

        $this->pageRenderer->addJsInlineCode($filePath, $javascript);
    }

    /**
     * Returns localization variables within an array
     *
     * @param string $filePath
     *
     * @return array
     * @throws \Exception
     */
    protected function getLocalizations($filePath)
    {
        global $LANG;
        global $LOCAL_LANG;

        // Language inclusion
        $LOCAL_LANG = $LANG->includeLLFile($filePath);
        if (!isset($LOCAL_LANG[$LANG->lang]) || empty($LOCAL_LANG[$LANG->lang])) {
            $lang = 'default';
        } else {
            $lang = $LANG->lang;
        }

        foreach ($LOCAL_LANG[$lang] as $key => $value) {
            $target = $value[0]['target'];

            // Replace '.' in key because it would break JSON
            $key = str_replace('.', '_', $key);
            $result[$key] = $target;
        }

        return $result;
    }
}
