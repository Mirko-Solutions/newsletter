define(
    [
        'jquery',
        'TYPO3/CMS/Backend/Notification',
        'TYPO3/CMS/Newsletter/Libraries/Grid',
        'TYPO3/CMS/Newsletter/Libraries/Libraries',
        'TYPO3/CMS/Newsletter/Libraries/Utility',
    ], function ($, Notification, agGrid, Libraries, Utility) {
        const Statistics = function () {
            const me = this;

            me.getNewsletterList = (gridOptions) => {
                const params = getBackendRequest(
                    'web',
                    'tx_newsletter_m1',
                    'Newsletter',
                    'list'
                );
                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        gridOptions.api.setRowData(response.data);
                        gridOptions.api.forEachNode(node => node.rowIndex === 0 ? node.setSelected(true) : node.setSelected(false));
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

            me.getNewsletterStatistics = () => {
                const params = getBackendRequest(
                    'web',
                    'tx_newsletter_m1',
                    'Newsletter',
                    'statistics',
                    {
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
                const params = getBackendRequest(
                    'web',
                    'tx_newsletter_m1',
                    'Email',
                    'list',
                    {
                        uidNewsletter: newsletter['__identity'],
                        start: 0,
                        limit: 500,
                    });

                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        me.emailStatsTable.api.setRowData(response.data);
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
                const params = getBackendRequest(
                    'web',
                    'tx_newsletter_m1',
                    'Link',
                    'list',
                    {
                        uidNewsletter: newsletter['__identity'],
                        start: 0,
                        limit: 500,
                    });

                $.ajax({
                    url: moduleUrl,
                    data: params,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Content-Type', 'json');
                    },
                    success: function (response) {
                        me.linksStatsTable.api.setRowData(response.data);
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
            statistics.linksStatsTable = loadLinksStatsTables();
            statistics.emailStatsTable = loadEmailStatsTables();
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

        function loadLinksStatsTables() {
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

            return gridOptions;
        }

        function loadEmailStatsTables() {
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

            return gridOptions;
        }

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
            chart.data.labels = [];
            chart.data.datasets = [];
            const emailNotSentCount = [];
            const emailSentCount = [];
            const emailOpenedCount = [];
            const emailBouncedCount = [];
            const linkOpenedCount = [];
            let date = '';
            $.each(stats, function (name, value) {
                const newDate = new Date(value.time * 1000).toLocaleTimeString([], {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });

                if (newDate !== date) {
                    chart.data.labels.push(newDate)
                } else {
                    chart.data.labels.push('')
                }

                date = newDate;
                emailNotSentCount.push(value['emailNotSentCount'])
                emailSentCount.push(value['emailSentCount'])
                emailOpenedCount.push(value['emailOpenedCount'])
                emailBouncedCount.push(value['emailBouncedCount'])
                linkOpenedCount.push(value['linkOpenedCount'])
            });
            updateChartDatasets(
                chart,
                'emailSentCount',
                emailSentCount,
                'rgba(54, 162, 235, 1)',
                'rgb(44,116,128)',
                true,
                4
            );
            updateChartDatasets(
                chart,
                'emailNotSentCount',
                emailNotSentCount,
                'rgba(145,145,145, 1)',
                'rgb(121,121,121)',
                true,
                3
            );
            updateChartDatasets(
                chart,
                'emailOpenedCount',
                emailOpenedCount,
                'rgba(46,227,54, 1)',
                'rgb(28,140,33)',
                true,
                10
            );
            updateChartDatasets(
                chart,
                'emailBouncedCount',
                emailBouncedCount,
                'rgba(255,86,86,1)',
                'rgb(131,45,45)',
                true,
                1
            );

            updateChartDatasets(
                chart,
                'linkOpenedCount',
                linkOpenedCount,
                'rgba(255, 205, 86, 1)',
                'rgb(124,99,41)',
                true,
                0
            );

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
                            tooltipFormat: 'DD T',
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