define(
    [
        'jquery',
        'TYPO3/CMS/Backend/Notification',
        'TYPO3/CMS/Newsletter/Libraries/Grid',
        'TYPO3/CMS/Newsletter/Libraries/Libraries'
    ], function ($, Notification, agGrid, libraries) {
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

        const Statistics = function () {
            const me = this;
            const extKey = 'newsletter';
            let selectedNewsletter = {};
            let overviewChart;
            let timeLineChart;

            me.getNewsletterList = (gridOptions) => {
                const params = me.getBackendRequest('web', 'tx_newsletter_m1', 'Newsletter', 'list');
                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        gridOptions.api.setRowData(response.data);
                        gridOptions.api.forEachNode(node => node.rowIndex === 0 ? node.setSelected(true) : node.setSelected(false));
                        generateFlashMessageFromResponse(response);
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

            me.getBackendRequest = (mainModuleName, subModuleName, controller, action, parameters = {}) => {
                const parameterPrefix = me.getParameterPrefix(mainModuleName, subModuleName);
                const params = {};

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

            me.getNewsletterStatistics = () => {
                const params = me.getBackendRequest('web', 'tx_newsletter_m1', 'Newsletter', 'statistics', {
                    uidNewsletter: me.selectedNewsletter['__identity']
                });
                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        const newsletter = response.data;
                        const AllStats = newsletter['statistics'];
                        const lastSentStats = AllStats[AllStats.length - 1];
                        updatePieChartData(
                            me.overviewChart,
                            me.selectedNewsletter['__identity'],
                            [
                                lastSentStats['emailNotSentCount'],
                                lastSentStats['emailSentCount'],
                                lastSentStats['emailOpenedCount'],
                                lastSentStats['emailBouncedCount']
                            ]
                        )
                        $('.overview__recipients-count span').html(lastSentStats['emailCount']);
                        $('.overview__emailsOpened span').html(lastSentStats['emailOpenedPercentage']);
                        $('.overview__emailsBounced span').html(lastSentStats['emailBouncedPercentage']);
                        $('.overview__clickedLinks span').html(lastSentStats['linkOpenedPercentage']);
                        const plannedDate = new Date(newsletter['plannedTime'])
                        const beginTime = new Date(newsletter['beginTime'])
                        $('.overview__plannedDate span').html(plannedDate.toLocaleString("en-GB"));
                        $('.overview__startDate span').html(beginTime.toLocaleString("en-GB"));

                        updateTimeLineChartData(me.timeLineChart, AllStats)
                        me.getNewsletterEmailsList(newsletter);
                        me.getNewsletterLinksList(newsletter);
                    },
                    error: function (response) {
                        const r = $.parseJSON(response.responseText);
                        Notification.error(r.message);
                    },
                    done: function () {
                        console.log('d1');
                    }
                });
            }

            me.getNewsletterEmailsList = (newsletter) => {
                const params = me.getBackendRequest('web', 'tx_newsletter_m1', 'Email', 'list', {
                    uidNewsletter: newsletter['__identity'],
                    start: 0,
                    limit: 500,
                });

                const eGridDiv = document.getElementById("EmailList");
                const gridOptions = {
                    columnDefs: [
                        {
                            headerName: 'id',
                            field: "__identity"
                        },
                        {
                            headerName: 'Recipient address',
                            field: "recipientAddress"
                        },
                        {
                            headerName: 'Sent',
                            field: "beginTime",
                            cellRenderer: (data) => {
                                return data.value ? (new Date(data.value)).toLocaleDateString() : '';
                            }
                        },
                        {
                            headerName: 'Open time',
                            field: "openTime",
                            cellRenderer: (data) => {
                                return data.value ? (new Date(data.value)).toLocaleDateString() : '';
                            }
                        },
                        {
                            headerName: 'Bounce time',
                            field: "openTime",
                            cellRenderer: (data) => {
                                return data.value ? (new Date(data.value)).toLocaleDateString() : '';
                            }
                        },
                        {
                            headerName: 'Unsubscribed',
                            field: "unsubscribed"
                        },
                        {
                            headerName: "View",
                            cellRenderer: (data) => {
                                return '<a href="' + emailShowUrl + ' \'&type=1342671779&injectOpenSpy=0&injectLinksSpy=0&c=' + data.data['authCode'] + '">view</a>';
                            }
                        },
                    ],

                    defaultColDef: {sortable: true, filter: true},
                    pagination: true,
                    // sets 10 rows per page (default is 100)
                    paginationPageSize: 10,
                    animateRows: true,
                };
                new agGrid.Grid(eGridDiv, gridOptions);

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
            }

            me.getNewsletterLinksList = (newsletter) => {
                const params = me.getBackendRequest('web', 'tx_newsletter_m1', 'Link', 'list', {
                    uidNewsletter: newsletter['__identity'],
                    start: 0,
                    limit: 500,
                });

                const eGridDiv = document.getElementById("LinksList");
                const gridOptions = {
                    columnDefs: [
                        {
                            headerName: 'id',
                            field: "__identity"
                        },
                        {
                            headerName: '% of opened',
                            field: "openedPercentage",
                            cellRenderer: (data) => {
                                return data.value + ' %';
                            }

                        },
                        {
                            headerName: '# of opened',
                            field: "openedCount"
                        },
                        {
                            headerName: 'url',
                            field: "url"
                        },
                    ],

                    defaultColDef: {sortable: true, filter: true},
                    pagination: true,
                    // sets 10 rows per page (default is 100)
                    paginationPageSize: 10,
                    animateRows: true,
                };
                new agGrid.Grid(eGridDiv, gridOptions);

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
            }
        };


        $(document).ready(function () {
            const statistics = new Statistics();

            // Grid Options are properties passed to the grid
            const gridOptions = {
                columnDefs: [
                    {
                        headerName: 'newsletter id',
                        field: "__identity"
                    },
                    {
                        field: "title"
                    },
                    {
                        field: "plannedTime",
                        cellRenderer: (data) => {
                            return data.value ? (new Date(data.value)).toLocaleDateString() : '';
                        }
                    },
                    {
                        field: "beginTime",
                        cellRenderer: (data) => {
                            return data.value ? (new Date(data.value)).toLocaleDateString() : '';
                        }
                    },
                    {
                        field: "emailCount"
                    },
                    {
                        field: "isTest",
                        cellRenderer: (data) => {
                            return data ? '✔' : '';
                        }
                    },
                ],

                defaultColDef: {sortable: true, filter: true},
                pagination: true,
                rowSelection: 'single',
                // sets 10 rows per page (default is 100)
                paginationPageSize: 10,
                animateRows: true,
                onSelectionChanged: () => {
                    const selectedData = gridOptions.api.getSelectedRows();
                    statistics.selectedNewsletter = selectedData[0]
                    statistics.getNewsletterStatistics()
                },
            };

            const eGridDiv = document.getElementById("NewsletterList");
            new agGrid.Grid(eGridDiv, gridOptions);

            statistics.getNewsletterList(gridOptions);
            statistics.overviewChart = loadOverviewStatsChart();
            statistics.timeLineChart = loadTimelineStatsChart();
        });

        //Stats Tabs
        $(document).ready(function () {
            const allTabsButons = $(".tabs__button");
            const allTabs = $(".tabs .tab");

            allTabsButons.click(function () {
                const thisParentCityAttr = $(this).parent().attr("data-tab");

                allTabsButons.parent().removeClass("tabs__item_active");
                $(this).parent().addClass("tabs__item_active");

                allTabs.filter(".tab_active").removeClass("tab_active");
                allTabs.filter(`[data-tab=${thisParentCityAttr}]`).addClass("tab_active");
            });
        });

        function updatePieChartData(chart, label, data) {
            chart.data.datasets.pop();
            chart.data.datasets.push({
                label: label,
                data: data,
                backgroundColor: [
                    'rgb(145,145,145)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(46,227,54)'
                ],
                hoverOffset: 4
            });
            chart.update();
        }

        function updateTimeLineChartData(chart, stats) {
            chart.data.labels.pop();
            chart.data.datasets.pop();
            const emailNotSentCount = [];
            const emailSentCount = [];
            const emailOpenedCount = [];
            const emailBouncedCount = [];
            const linkOpenedCount = [];
            $.each(stats, function (name, value) {
                chart.data.labels.push(new Date(value.time * 1000).toLocaleString("en-GB"));
                emailNotSentCount.push(value['emailNotSentCount'])
                emailSentCount.push(value['emailSentCount'])
                emailOpenedCount.push(value['emailOpenedCount'])
                emailBouncedCount.push(value['emailBouncedCount'])
                linkOpenedCount.push(value['linkOpenedCount'])
            });

            chart.data.datasets.push({
                label: [
                    'emailSentCount',
                ],
                data: emailSentCount,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.5)',
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                ],
                fill: true,
                hoverOffset: 4
            })
            chart.data.datasets.push({
                label: [
                    'emailNotSentCount',
                ],
                data: emailNotSentCount,
                backgroundColor: [
                    'rgba(145,145,145, 0.5)',
                ],
                borderColor: [
                    'rgba(145,145,145, 1)',
                ],
                fill: true,
                hoverOffset: 0
            })
            chart.data.datasets.push({
                label: [
                    'emailOpenedCount',
                ],
                data: emailOpenedCount,
                backgroundColor: [
                    'rgba(46,227,54, 0.5)',
                ],
                borderColor: [
                    'rgba(46,227,54, 1)',
                ],
                fill: true,
                hoverOffset: 1
            })
            chart.data.datasets.push({
                label: [
                    'emailBouncedCount',
                ],
                data: emailBouncedCount,
                backgroundColor: [
                    'rgba(255,86,86,0.5)',
                ],
                borderColor: [
                    'rgba(255,86,86,1)',
                ],
                fill: true,
                hoverOffset: 2
            })
            chart.data.datasets.push({
                label: [
                    'linkOpenedCount',
                ],
                data: linkOpenedCount,
                backgroundColor: [
                    'rgba(255, 205, 86, 0.5)',
                ],
                borderColor: [
                    'rgba(255, 205, 86, 1)',
                ],
                fill: true,
                hoverOffset: 3
            })
            chart.update();
        }

        function loadOverviewStatsChart() {
            const ctx = document.getElementById('overviewStatsPieChart');
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ["not yet sent", "sent", "opened", "bounced"],
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function loadTimelineStatsChart() {
            const ctx = document.getElementById('timelineChart');
            return new Chart(ctx, {
                type: 'line',
                options: {
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    x: {
                        type: 'time',
                        time: {
                            // Luxon format string
                            tooltipFormat: 'DD T'
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'value'
                        },
                        stacked: true
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });