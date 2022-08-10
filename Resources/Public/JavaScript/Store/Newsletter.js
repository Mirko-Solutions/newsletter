(function () {
    'use strict';

    Ext.ns('Ext.ux.Mirko.Newsletter.Store');

    /**
     * A Store for the movie model using ExtDirect to communicate with the
     * server side extbase framework.
     */
    Ext.ux.Mirko.Newsletter.Store.Newsletter = (function () {

        var newsletterStore = null;

        var initialize = function () {
            if (newsletterStore === null) {
                newsletterStore = new Ext.data.DirectStore({
                    storeId: 'Mirko\\Newsletter\\Domain\\Model\\Newsletter',
                    reader: new Ext.data.JsonReader({
                        totalProperty: 'total',
                        successProperty: 'success',
                        idProperty: '__identity',
                        root: 'data',
                        fields: [
                            {name: '__identity', type: 'int'},
                            {name: 'pid', type: 'int'},
                            {name: 'beginTime', type: 'date'},
                            {name: 'endTime', type: 'date'},
                            {name: 'injectLinksSpy', type: 'boolean'},
                            {name: 'injectOpenSpy', type: 'boolean'},
                            {name: 'isTest', type: 'boolean'},
                            {name: 'plannedTime', type: 'date'},
                            {name: 'repetition', type: 'int'},
                            {name: 'senderEmail', type: 'string'},
                            {name: 'senderName', type: 'string'},
                            {name: 'replytoEmail', type: 'string'},
                            {name: 'replytoName', type: 'string'},
                            {name: 'plainConverter', type: 'string'},
                            {name: 'title', type: 'string'},
                            {name: 'uidBounceAccount', type: 'int'},
                            {name: 'uidRecipientList', type: 'int'},
                            {name: 'emailCount', type: 'int'},
                            {name: 'statistics'},
                        ],
                    }),
                    writer: new Ext.data.JsonWriter({
                        encode: false,
                        writeAllFields: false,
                    }),
                    api: {
                        read: Ext.ux.Mirko.Newsletter.Remote.NewsletterController.listAction,
                        update: Ext.ux.Mirko.Newsletter.Remote.NewsletterController.updateAction,
                        destroy: Ext.ux.Mirko.Newsletter.Remote.NewsletterController.deleteAction,
                        create: Ext.ux.Mirko.Newsletter.Remote.NewsletterController.createAction,
                    },
                    paramOrder: {
                        read: [],
                        update: ['data'],
                        create: ['data'],
                        destroy: ['data'],
                    },
                    autoLoad: true,
                    restful: false,
                    batch: false,
                    remoteSort: false,
                });
            }
        };

        /**
         * Public API of this singleton.
         */
        return {
            initialize: initialize,
        };
    }());
}());
