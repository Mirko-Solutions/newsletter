<?php

namespace Mirko\Newsletter\ViewHelpers;

use Mirko\Newsletter\MVC\ExtDirect\Api;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * View helper which allows
 *
 * = Examples =
 *
 * <newsletter:be.moduleContainer pageTitle="foo">
 *    <newsletter:includeDirectApi />
 * </newsletter:be.moduleContainer>
 */
class ExtDirectProviderViewHelper extends AbstractViewHelper
{
    /**
     * @var Api
     */
    private Api $apiService;

    public function __construct(Api $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @see Classes/Core/ViewHelper/\TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper#initializeArguments()
     */
    public function initializeArguments()
    {
        $this->registerArgument('name', 'string', 'Name', false, 'remoteDescriptor');
        $this->registerArgument('namespace', 'string', 'Namespace', false, 'Ext.ux.Mirko.Newsletter.Remote');
        $this->registerArgument('routeUrl', 'string', 'Route URL', false, null);
    }

    /**
     * Generates a Ext.Direct API descriptor and adds it to the pagerenderer.
     * Also calls Ext.Direct.addProvider() on itself (at js side).
     * The remote API is directly useable.
     *
     */
    public function render()
    {
        $name = $this->arguments['name'];
        $namespace = $this->arguments['namespace'];
        $routeUrl = $this->arguments['routeUrl'];

        if ($routeUrl === null) {
            $routeUrl = $this->renderingContext->getUriBuilder()->reset()->build(
                ) . '&Mirko\\Newsletter\\ExtDirectRequest=1';
        }

        $api = $this->apiService->createApi($routeUrl, $namespace);

        // prepare output variable
        $jsCode = '';
        $descriptor = $namespace . '.' . $name;
        // build up the output
        $jsCode .= 'Ext.onReady(function () {' . "\n";
        $jsCode .= 'Ext.ns(\'' . $namespace . '\'); ' . "\n";
        $jsCode .= $descriptor . ' = ';
        $jsCode .= json_encode($api);
        $jsCode .= ";\n";
        $jsCode .= 'Ext.Direct.addProvider(' . $descriptor . ');' . "\n";
        $jsCode .= '});' . "\n";
        // add the output to the pageRenderer
        $this->pageRenderer->addJsInlineCode($name, $jsCode, true, true);
    }
}
