(function () {
    'use strict';

    Ext.ns('Ext.ux.Mirko.Newsletter.Module');

    /**
     * @class Ext.ux.Mirko.Newsletter.Module.Application
     * @namespace Ext.ux.Mirko.Newsletter.Module
     * @extends Ext.util.Observable
     *
     * The main entry point which controls the lifecycle of the application.
     *
     * This is the main event handler of the application.
     *
     * @singleton
     */

    Ext.ux.Mirko.Newsletter.Module.Application = Ext.apply(new Ext.util.Observable(), {
        /**
         * Main bootstrap. This is called by Ext.onReady.
         *
         * This method is called automatically.
         */
        bootstrap: function () {
            Ext.QuickTips.init();

            // init Flashmessage
            Ext.ux.Mirko.Newsletter.DirectFlashMessageDispatcher.initialize();
            Ext.ux.Mirko.Newsletter.FlashMessageOverlayContainer.initialize({
                minDelay: 5,
                maxDelay: 15,
                logLevel: -1,
                opacity: 1,
            });

            if (this.checkIfPage()) {
                this.initStore();
                this.initGui();
                /* Insert typo3-docheaders to get a consistent backend-module-look */
                Ext.get('main-tabs').insertHtml('beforeBegin', '<div class="typo3-docheader-functions"></div>');
                Ext.get('main-tabs').insertHtml('beforeBegin', '<div class="typo3-docheader-buttons"></div>');
            } else if (this.checkIfPageIsFolder()) {
                this.initFolderGui();
            } else {
                this.initHelpGui();
            }
        },
        /**
         * Check if the application can be loaded
         *
         * @return {Boolean}
         */
        checkIfPage: function () {
            return Ext.ux.Mirko.Newsletter.Configuration.pageType == 'page';
        },
        /**
         * Check if the application can be loaded
         *
         * @return {Boolean}
         */
        checkIfPageIsFolder: function () {
            return Ext.ux.Mirko.Newsletter.Configuration.pageType == 'folder';
        },
        /**
         * Init menus and content area
         */
        initGui: function () {

            return new Ext.Viewport({
                layout: 'fit',
                renderTo: Ext.getBody(),
                items: [{
                    id: 'main-tabs',
                    xtype: 'tabpanel',
                    activeTab: 0,
                    border: false,
                    headerCssClass: 't3-newsletter-docheader',
                    bodyCssClass: 't3-newsletter-docbody',
                    padding: '0 20px 10px 24px',
                    items: [{
                        xtype: 'Ext.ux.Mirko.Newsletter.Planner.Planner',
                        iconCls: 't3-newsletter-button-planner',
                        api: {
                            load: Ext.ux.Mirko.Newsletter.Remote.NewsletterController.listPlannedAction,
                            submit: Ext.ux.Mirko.Newsletter.Remote.NewsletterController.createAction,
                        },

                    }, {
                        xtype: 'Ext.ux.Mirko.Newsletter.Statistics.Statistics',
                        iconCls: 't3-newsletter-button-statistics',
                    }],
                }],
            });
        },
        /**
         * Init ExtDirect stores
         */
        initStore: function () {
            Ext.ux.Mirko.Newsletter.Store.Newsletter.initialize();
            Ext.ux.Mirko.Newsletter.Store.SelectedNewsletter.initialize();
            Ext.ux.Mirko.Newsletter.Store.PlannedNewsletter.initialize();
            Ext.ux.Mirko.Newsletter.Store.Email.initialize();
            Ext.ux.Mirko.Newsletter.Store.Link.initialize();
            Ext.ux.Mirko.Newsletter.Store.BounceAccount.initialize();
            Ext.ux.Mirko.Newsletter.Store.RecipientList.initialize();
            Ext.ux.Mirko.Newsletter.Store.Recipient.initialize();
        },
        /**
         * Init folder GUI
         */
        initFolderGui: function () {

            return new Ext.Viewport({
                layout: 'fit',
                renderTo: Ext.getBody(),
                items: [
                    {
                        height: 500,
                        xtype: 'panel',
                        html: [
                            '<div id="typo3-docheader">',
                            '<div id="typo3-docheader-row1">&nbsp;</div>',
                            '<div id="typo3-docheader-row2">&nbsp;</div>',
                            '</div>',
                            '<div id="typo3-docbody">',
                            '<div id="typo3-inner-docbody">',
                            '<h2>' + Ext.ux.Mirko.Newsletter.Language.message_title_page_selected + '</h2>',
                            '<p>' + Ext.ux.Mirko.Newsletter.Language.message_page_selected + '</p>',
                            '</div>',
                            '</div>',
                        ],
                    },
                ],
            });
        },
        /**
         * Init help Gui
         */
        initHelpGui: function () {

            return new Ext.Viewport({
                layout: 'fit',
                renderTo: Ext.getBody(),
                items: [
                    {
                        height: 500,
                        xtype: 'panel',
                        html: [
                            '<div id="typo3-docheader">',
                            '<div id="typo3-docheader-row1">&nbsp;</div>',
                            '<div id="typo3-docheader-row2">&nbsp;</div>',
                            '</div>',
                            '<div id="typo3-docbody">',
                            '<div id="typo3-inner-docbody">',
                            '<h2>' + Ext.ux.Mirko.Newsletter.Language.message_title_no_pid_selected + '</h2>',
                            '<p>' + Ext.ux.Mirko.Newsletter.Language.message_no_pid_selected + '</p>',
                            '</div>',
                            '</div>',
                        ],
                    },
                ],
            });
        },
    });
}());
