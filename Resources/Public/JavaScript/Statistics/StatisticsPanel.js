(function () {
    'use strict';

    Ext.ns('Ext.ux.Mirko.Newsletter.Statistics');

    /**
     * @class Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel
     * @namespace Ext.ux.Mirko.Newsletter.Statistics
     * @extends Ext.TabPanel
     *
     * Class for statistic tab panel
     */
    Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel = Ext.extend(Ext.TabPanel, {
        initComponent: function () {

            var config = {
                activeTab: 0,
                border: false,
                items: [
                    {
                        title: Ext.ux.Mirko.Newsletter.Language.overview_tab,
                        xtype: 'Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.OverviewTab',
                        itemId: 'overviewTab',
                    },
                    {
                        title: Ext.ux.Mirko.Newsletter.Language.emails_tab,
                        xtype: 'Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.EmailTab',
                        itemId: 'emailTab',
                    },
                    {
                        title: Ext.ux.Mirko.Newsletter.Language.links_tab,
                        xtype: 'Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.LinkTab',
                        itemId: 'linkTab',
                    },
                ],
            };
            Ext.apply(this, config);
            Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel.superclass.initComponent.call(this);
        },

    });

    Ext.reg('Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel', Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel);
}());
