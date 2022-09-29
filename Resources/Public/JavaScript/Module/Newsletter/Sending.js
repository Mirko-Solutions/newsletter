define([
    'jquery',
    'TYPO3/CMS/Backend/Notification'], function ($, Notification) {

    const NewsLetterSending = function () {
        const me = this;
        const extKey = 'newsletter';

        me.getListRecipient = function ($id) {
            var params = me.getBackendRequest('web', 'tx_newsletter_m1', 'RecipientList', 'listRecipient', {
                uidRecipientList: $id,
                start: 0,
                limit: 10
            });
            $.ajax({
                url: moduleUrl,
                data: params,
                beforeSend: function(xhr){xhr.setRequestHeader('Content-Type', 'json');},
                success: function (response) {
                    console.log(response)
                },
                error: function (response) {
                    const r = $.parseJSON(response.responseText);
                    Notification.error(r.message);
                },
                done: function () {
                    console.log('d1');
                }
            });
        };

        me.getBackendRequest = function (mainModuleName, subModuleName, controller, action, parameters) {
            var parameterPrefix = me.getParameterPrefix(mainModuleName, subModuleName);
            var params = {};

            parameters['controller'] = controller;
            parameters['action'] = action;

            $.each(parameters, function (name, value) {
                params[parameterPrefix + '[' + name + ']'] = value;
            });

            return params;
        };

        me.getParameterPrefix = function (mainModuleName, subModuleName) {
            return 'tx_' + extKey + '_' + mainModuleName + '_' + extKey + subModuleName.replace(/_/g, '');
        };
    };

    $(document).ready(function () {
        const sender = new NewsLetterSending();

        $('#recipientListSelector').on('change', function () {
            var listId= $(this).val();
            if (listId !== '0') {
                sender.getListRecipient(listId);
            } else {
                console.log('nothing')
            }
        })
    });
});