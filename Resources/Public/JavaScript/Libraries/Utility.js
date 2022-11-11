const extKey = 'newsletter';

const getTimeZone = () => {
    const timezoneOffset = new Date().getTimezoneOffset()
    const offset = Math.abs(timezoneOffset)
    const offsetOperator = timezoneOffset < 0 ? '+' : '-'
    const offsetHours = (offset / 60).toString().padStart(2, '0')
    const offsetMinutes = (offset % 60).toString().padStart(2, '0')

    return `${offsetOperator}${offsetHours}:${offsetMinutes}`
}

const generateFlashMessageFromResponse = (Notification, response) => {
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

const getBackendRequest = (mainModuleName, subModuleName, controller, action, parameters = {}) => {
    var parameterPrefix = getParameterPrefix(mainModuleName, subModuleName);
    var params = {};

    parameters['controller'] = controller;
    parameters['action'] = action;

    $.each(parameters, function (name, value) {
        params[parameterPrefix + '[' + name + ']'] = value;
    });

    return params;
};

const getParameterPrefix = (mainModuleName, subModuleName) => {
    return 'tx_' + extKey + '_' + mainModuleName + '_' + extKey + subModuleName.replace(/_/g, '');
};

const updateChartDatasets = (chart, label, data, backgroundColor, borderColor, fill = false, hoverOffset = 0) => {
    chart.data.datasets.push({
        label: label,
        data: data,
        backgroundColor: backgroundColor,
        borderColor: borderColor,
        fill: fill,
        hoverOffset: hoverOffset
    })
}