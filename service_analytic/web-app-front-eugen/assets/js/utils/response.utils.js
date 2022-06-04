const parseResponseError = error => {
    let title = 'Произошла ошибка...';
    const response = error?.response.data || null;
    let messages = [];

    if (!response || !response.error) {
        messages = [
            'Произошла неизвестная ошибка. Не удалось обработать данные, пожалуйста, попробуйте обновить страницу',
        ];
    } else {
        const errorData = response.error;

        let advanced = null;
        if (errorData.advanced.length) {
            advanced = errorData.advanced;
        } else if (Object.values(errorData.advanced).length) {
            advanced = Object.values(errorData.advanced);
        }

        if (typeof errorData === 'string') {
            messages = [errorData];
        } else if (advanced) {
            if (errorData.message) {
                title = errorData.message;
            }

            messages = advanced.map(current => Object.values(current));
        } else {
            messages = [errorData.message];
        }
    }

    return { title, messages };
};

const parseResponseDone = () => {
    console.log('done');
};

const errorHandler = (error, notify) => {
    const output = parseResponseError(error);
    const callbacks = output.messages.map(message =>
        notify.create({
            message,
            type: 'negative',
        })
    );
    return { data: output, callbacks };
};

const showSuccessMessage = (message, notify) => {
    const callbacks = notify.create({
        message,
        type: 'positive',
    });
    return { callbacks };
};

export { parseResponseDone, parseResponseError, errorHandler, showSuccessMessage };
