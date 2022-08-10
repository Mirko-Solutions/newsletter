(function () {
    'use strict';

    Ext.ns('Ext.ux.Mirko.Newsletter.Statistics');

    /**
     * @class Ext.ux.Mirko.Newsletter.Statistics.Statistics
     * @namespace Ext.ux.Mirko.Newsletter.Statistics
     * @extends Ext.Container
     *
     * Class for statistic container
     */
    Ext.ux.Mirko.Newsletter.Statistics.Statistics = Ext.extend(Ext.Container, {
        initComponent: function () {
            var config = {
                layout: 'border',
                title: Ext.ux.Mirko.Newsletter.Language.statistics_tab,
                items: [
                    {
                        split: true,
                        region: 'north',
                        xtype: 'Ext.ux.Mirko.Newsletter.Statistics.NewsletterListMenu',
                        ref: 'newsletterListMenu',
                    },
                    {
                        region: 'center',
                        xtype: 'Ext.ux.Mirko.Newsletter.Statistics.StatisticsPanel',
                        ref: 'statisticsPanel',
                    },
                ],
            };
            Ext.apply(this, config);
            Ext.ux.Mirko.Newsletter.Statistics.Statistics.superclass.initComponent.call(this);
        },
    });

    Ext.reg('Ext.ux.Mirko.Newsletter.Statistics.Statistics', Ext.ux.Mirko.Newsletter.Statistics.Statistics);
}());
