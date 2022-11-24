define(
    [
        'jquery',
        'TYPO3/CMS/Backend/Notification',
        'TYPO3/CMS/Newsletter/Libraries/Utility',
        'TYPO3/CMS/Newsletter/Libraries/Grid'
    ], function ($, Notification, Utility, agGrid) {
        const NewsLetterSending = function () {
            const me = this;

            me.getListRecipient = function ($id, gridOptions) {
                var params = getBackendRequest('web', 'tx_newsletter_m1', 'RecipientList', 'listRecipient', {
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

                const params = getBackendRequest('web', 'tx_newsletter_m1', 'Newsletter', 'create', {
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

                        generateFlashMessageFromResponse(Notification, response);
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

            $('.extendButton').on('click', function (e) {
                $(this).next().toggleClass('hidden')
            })

            $('#sendTestEmail-action').on('click', function (e) {
                if (validateForm() || $(this).hasClass('disabled')) {
                    return;
                }
                sender.createNewsletter($(this), 1);
            })

            $('#addToQueue-action').on('click', function (e) {
                if (validateForm() || $(this).hasClass('disabled')) {
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

                if (error) {
                    Notification.error('Form validation error', '', 2);
                }

                return error;
            }

        });
    });