<?php

namespace Mirko\Newsletter\ViewHelpers;

/**
 * Makes an array of configuration available in JavaScript
 */
class ConfigurationViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('configuration', 'array', 'the list of configuration for the JS');
    }

    /**
     * Generates some more JS to be registered / delegated to the page renderer
     *
     */
    public function render()
    {
        $configuration = $this->arguments['configuration'];

        $configuration = json_encode($configuration);
        $javascript = "Ext.ux.Mirko.Newsletter.Configuration = $configuration;";

        $this->pageRenderer->addJsInlineCode('Ext.ux.Mirko.Newsletter.Configuration', $javascript);
    }
}
