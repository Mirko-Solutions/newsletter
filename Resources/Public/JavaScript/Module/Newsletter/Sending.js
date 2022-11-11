define(
    [
        'jquery',
        'TYPO3/CMS/Backend/Notification',
        'TYPO3/CMS/Newsletter/Libraries/Grid'
    ], function ($, Notification, agGrid) {
        const getTimeZone = () => {
            const timezoneOffset = new Date().getTimezoneOffset()
            const offset = Math.abs(timezoneOffset)
            const offsetOperator = timezoneOffset < 0 ? '+' : '-'
            const offsetHours = (offset / 60).toString().padStart(2, '0')
            const offsetMinutes = (offset % 60).toString().padStart(2, '0')

            return `${offsetOperator}${offsetHours}:${offsetMinutes}`
        }
        const generateFlashMessageFromResponse = (response) => {
            response.flashMessages.forEach((message) => {
                switch (message.severity) {
                    case -2:
                        Notification.notice(message.title, message.message);
                        break;
                    case -1:
                        Notification.info(message.title, message.message);
                        break;
                    case 0:
                        Notification.success(message.title, message.message);
                        break;
                    case 1:
                        Notification.warning(message.title, message.message);
                        break;
                    case 2:
                        Notification.error(message.title, message.message);
                }
            })
        }

        const NewsLetterSending = function () {
            const me = this;
            const extKey = 'newsletter';

            me.getListRecipient = function ($id, gridOptions) {
                var params = me.getBackendRequest('web', 'tx_newsletter_m1', 'RecipientList', 'listRecipient', {
                    uidRecipientList: $id,
                    start: 0,
                    limit: 0
                });
                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        gridOptions.api.setRowData(response.data);
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

            me.createNewsletter = function (button, isTest) {
                const newNewsletterObj = {};
                $.each(button.closest("form").serializeArray(), function (i, field) {
                    if (!field.name.startsWith('tx_newsletter_web_newslettertxnewsletterm1[newsletter]')) {
                        return;
                    }
                    const name = field.name.replace('tx_newsletter_web_newslettertxnewsletterm1[newsletter][', '').replace('[__identity]', '').replace(']', '')
                    let value = field.value;
                    if (name === 'plannedTime') {
                        value = value + ':00' + getTimeZone();
                    }
                    newNewsletterObj[name] = value;
                });
                newNewsletterObj['isTest'] = isTest

                const params = me.getBackendRequest('web', 'tx_newsletter_m1', 'Newsletter', 'create', {
                    newNewsletter: newNewsletterObj,
                });

                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        $('.action-button').addClass('disabled');
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        $('.action-button').removeClass('disabled');
                        try {
                            generateFlashMessageFromResponse(response);
                        } catch (e) {
                            // Notification.error('Error', 'something went wrong');
                        }
                    },
                    error: function (response) {
                        $('.action-button').removeClass('disabled');
                        Notification.error('Error', 'something went wrong', 5);
                    },
                });
            }
        };


        $(document).ready(function () {
            const sender = new NewsLetterSending();

            // Grid Options are properties passed to the grid
            const gridOptions = {
                columnDefs: [
                    {field: "email"},
                    {field: "plain_only"}
                ],

                defaultColDef: {sortable: true, filter: true},
                pagination: true,

                // sets 10 rows per page (default is 100)
                paginationPageSize: 10,
                animateRows: true,
            };

            const eGridDiv = document.getElementById("recipientListTable");
            new agGrid.Grid(eGridDiv, gridOptions);
            sender.getListRecipient($('#recipientListSelector').val(), gridOptions);
            $('#recipientListSelector').on('change', function () {
                var listId = $(this).val();
                if (listId !== '0') {
                    sender.getListRecipient(listId, gridOptions);
                } else {
                    console.log('nothing')
                }
            })

            $(".disabled").on('click', function (e) {
                e.preventDefault();
                return;
            })

            $('.extendButton').on('click', function (e) {
                $(this).next().toggleClass('hidden')
            })

            $('#sendTestEmail-action').on('click', function (e) {
                if (validateForm()) {
                    return;
                }

                sender.createNewsletter($(this), 1);
            })

            $('#addToQueue-action').on('click', function (e) {
                if (validateForm()) {
                    return;
                }

                sender.createNewsletter($(this), 0);
            })

            function validateForm() {
                const senderName = $('#senderName').val();
                const senderEmail = $('#senderEmail').val();
                const replyToEmail = $('#replytoEmail').val();
                let error = false;
                $(".error").remove();

                if (senderName.length < 1) {
                    $('#senderName').after('<span class="error">This field is required</span>');
                    error = true;
                }
                const regEx = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                const validSenderEmail = regEx.test(senderEmail);
                if (!validSenderEmail) {
                    error = true;
                    $('#senderEmail').after('<span class="error">Enter a valid email</span>');
                }
                if (replyToEmail.length > 1) {
                    const validReplyToEmail = regEx.test(replyToEmail);
                    if (!validReplyToEmail) {
                        error = true;
                        $('#replytoEmail').after('<span class="error">Enter a valid email</span>');
                    }
                }

                console.log(error)
                return error;
            }

        });
    });