(function () {
    'use strict';

    Ext.ns('Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel');

    /**
     * @class Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.LinkTab
     * @namespace Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel
     * @extends Ext.Container
     *
     * Class for statistic container
     */
    Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.LinkTab = Ext.extend(Ext.grid.GridPanel, {
        initComponent: function () {
            var config = {
                loadMask: true,
                autoExpandColumn: 'url',
                // store
                store: Ext.StoreMgr.get('Mirko\\Newsletter\\Domain\\Model\\Link'),
                // paging bar on the bottom
                bbar: new Ext.PagingToolbar({
                    pageSize: 50,
                    store: Ext.StoreMgr.get('Mirko\\Newsletter\\Domain\\Model\\Link'),
                    displayInfo: true,
                    listeners: {
                        // Before we change page, we inject the currently selected newsletter as params for Ajax request
                        beforechange: function (pagingToolbar, params) {
                            var selectedNewsletterStore = Ext.StoreMgr.get('Mirko\\Newsletter\\Domain\\Model\\SelectedNewsletter');
                            var newsletter = selectedNewsletterStore.getAt(0);
                            params.data = newsletter.data.__identity;
                        },
                    },
                }),
                // column model
                columns: [
                    {
                        dataIndex: '__identity',
                        header: Ext.ux.Mirko.Newsletter.Language.link_id,
                        sortable: true,
                        width: 40,
                    },
                    {
                        dataIndex: 'openedPercentage',
                        header: Ext.ux.Mirko.Newsletter.Language.percentage_of_opened,
                        width: 100,
                        sortable: true,
                        css: 'text-align: center;',
                        renderer: this._renderPercentageOfOpened,
                    },
                    {
                        dataIndex: 'openedCount',
                        header: Ext.ux.Mirko.Newsletter.Language.number_of_opened,
                        width: 100,
                        sortable: true,
                        css: 'text-align: center;',
                    },
                    {
                        id: 'url',
                        dataIndex: 'url',
                        header: Ext.ux.Mirko.Newsletter.Language.tx_newsletter_domain_model_link_url,
                        sortable: true,
                        width: 600,
                        renderer: this._renderUrl,
                    },
                ],
            }; // eo config object

            Ext.apply(this, config);
            Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.LinkTab.superclass.initComponent.call(this);
        },
        /**
         * Renders the "called from" column
         *
         * @access private
         * @method _renderPercentageOfOpened
         * @param {string} value
         * @return string
         */
        _renderPercentageOfOpened: function (value) {
            return String.format('{0}%', value);
        },
        _renderUrl: function (value) {
            return String.format('<a href="{0}">{0}</a>', value);
        },
    });

    Ext.reg('Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.LinkTab', Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.LinkTab);
}());
