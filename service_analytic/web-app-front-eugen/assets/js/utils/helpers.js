/* eslint-disable */
export const keyCodes = Object.freeze({
    enter: 13,
    tab: 9,
    delete: 46,
    esc: 27,
    space: 32,
    up: 38,
    down: 40,
    left: 37,
    right: 39,
    end: 35,
    home: 36,
    del: 46,
    backspace: 8,
    insert: 45,
    pageup: 33,
    pagedown: 34,
    shift: 16,
});

export function getField(data, field) {
    if (typeof data !== 'object' && !Object.keys(data)) {
        throw new Error('Data is an object or has no keys');
    }

    if (!field) {
        throw new Error('field cannot be empty');
    }

    field = field.split('.');

    if (field.length === 1) {
        return data[field];
    }

    let link;
    field.forEach((value, index, array) => {
        if (index === 0) {
            link = data[value];
        } else {
            link = link[value];
        }
    });
    return link;
}
export const sortNumValuesAgGrid = (valueA, valueB, nodeA, nodeB, isInverted) => valueB - valueA;
export function setField(state, { value, field }) {
    state[field] = value;
}

export function isUnset(val) {
    return typeof val === 'undefined' || val === null;
}

export function isSet(val) {
    return !isUnset(val);
}

export function omit(obj, props) {
    const result = { ...obj };
    props.forEach(function (prop) {
        delete result[prop];
    });
    return result;
}

let passiveSupported = false;
try {
    if (typeof window !== 'undefined') {
        const testListenerOpts = Object.defineProperty({}, 'passive', {
            get: () => {
                passiveSupported = true;
            },
        });

        window.addEventListener('testListener', testListenerOpts, testListenerOpts);
        window.removeEventListener('testListener', testListenerOpts, testListenerOpts);
    }
} catch (e) {
    console.warn(e);
}
export { passiveSupported };

export function addPassiveEventListener(el, event, cb, options = {}) {
    el.addEventListener(event, cb, passiveSupported ? options : false);
}

export function replaceVariablesInTemplate(str, params) {
    if (!str) {
        return '';
    }
    return str.replace(/\{(\d+)\}/g, (match, index) => String(params[Number(index)]));
}

export function isObject(obj) {
    return obj !== null && typeof obj === 'object';
}

export function mergeDeep(source, target) {
    for (const key in target) {
        const sourceProperty = source[key];
        const targetProperty = target[key];

        // Only continue deep merging if
        // both properties are objects
        if (isObject(sourceProperty) && isObject(targetProperty)) {
            source[key] = mergeDeep(sourceProperty, targetProperty);

            continue;
        }

        source[key] = targetProperty;
    }

    return source;
}
export function getCookie(name) {
    const decodedCookie = decodeURIComponent(document.cookie);
    const ca = decodedCookie.trim().split(';');
    let value = '';
    ca.forEach(item => {
        const param = item.split('=');
        if (param[0].trim() === name) {
            value = param[1];
        }
    });
    return value;
}

export function sortObjectByAbc(field) {
    return function (a, b) {
        if (a[field] > b[field]) {
            return 1;
        }
        if (a[field] < b[field]) {
            return -1;
        }
        // a должно быть равным b
        return 0;
    };
}

export function getCookieFromString(cookie, name, decode = true) {
    const decodedCookie = decode ? decodeURIComponent(cookie) : cookie;
    const ca = decodedCookie.trim().split(';');
    let value;
    ca.forEach(item => {
        const param = item.split('=');
        if (param[0].trim() === name) {
            value = param[1];
        }
    });
    return value;
}

export function capitalizeFirstLetter(string) {
    if (!string?.length) {
        return '';
    }
    return string.charAt(0).toUpperCase() + string.slice(1);
}

export function initial(string) {
    if (!string?.length) {
        return '';
    }
    return string.charAt(0).toUpperCase();
}
export function upperCaseFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

const tagsToReplace = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
};

export function escapeHTML(str) {
    return str.replace(/[&<>]/g, tag => tagsToReplace[tag] || tag);
}

export function userNameWithInitialsFormatted(surname, name, patronymic) {
    // const { surname, name, patronymic } = this.$store.state.auth.user;
    const surnameCapitalized = capitalizeFirstLetter(surname);
    const nameInitial = initial(name);
    const patronymicInitial = initial(patronymic);
    let userName = '';
    if (surnameCapitalized) {
        userName += surnameCapitalized;
    }
    if (nameInitial) {
        userName += ` ${nameInitial}.`;
    }
    if (patronymicInitial) {
        userName += `${patronymicInitial}.`;
    }
    return userName;
}

export function getObjectFromArray(key, array) {
    const newObject = {};
    array.forEach(el => {
        try {
            if (el[key]) {
                newObject[el[key].toString().toLowerCase()] = el;
            }
            // TODO: Лишняя проверка
        } catch (err) {
            console.error(err);
        }
    });
    return newObject;
}

export function getCoords(elem, client) {
    const box = elem.getBoundingClientRect();

    return client
        ? {
              top: box.top,
              left: box.left,
          }
        : {
              top: box.top + window.pageYOffset,
              left: box.left + window.pageXOffset,
          };
}

export function getDeclWord(words, value) {
    value = Math.abs(value) % 100;
    const num = value % 10;
    if (value > 10 && value < 20) return words[2];
    if (num > 1 && num < 5) return words[1];
    if (num === 1) return words[0];
    return words[2];
}

export function formatDate(dateNf) {
    const [date, time] = dateNf.split(' ');
    const [year, month, day] = date.split('-');
    return [day, month, year].join('.');
}
export function errorParser(context, error, typeOuput) {
    if (!context || !error) return;

    try {
        const { advanced, message } = error;
        // Проверка на наличие элементов в массиве Advanced
        const conclOnAdvanced = advanced.length && Object.keys(advanced[0]).length;
        // Если тип вывода во всплывающих подсказаках
        // TODO: Сделать тип для подсветки соответсвующих полей, когда названия полей будут совпадать с бэком
        if (conclOnAdvanced) {
            const errorsList = advanced.map(item => item[Object.keys(item)[0]]);

            errorsList.forEach(error => {
                context.$notify.create({
                    message: error,
                    type: 'negative',
                });
            });
        } else {
            context.$notify.create({
                message: message,
                type: 'negative',
            });
        }
    } catch (error) {
        console.error('Error while displaying an error', error);
    }
}

export function getMaskedCharacters(text, searchString) {
    if (!searchString) {
        return text;
    }
    const search = (searchString || '').toString().toLowerCase();

    const index = text.toLowerCase().indexOf(search);

    if (index < 0) {
        return { start: text, middle: '', end: '' };
    }

    const start = text.slice(0, index);
    const middle = text.slice(index, index + search.length);
    const end = text.slice(index + search.length);
    return { start, middle, end };
}

export function genFilteredText(text, searchString) {
    text = text || '';

    if (!searchString) {
        return escapeHTML(text);
    }

    const { start, middle, end } = getMaskedCharacters(text, searchString);

    return `${escapeHTML(start)}${genHighlight(middle)}${escapeHTML(end)}`;
}

export function genHighlight(text) {
    return `<span class="accent--text">${escapeHTML(text)}</span>`;
}
export function getShortNumber(num, digits) {
    const lookup = [
        { value: 1, symbol: '' },
        { value: 1e3, symbol: 'к' },
        { value: 1e6, symbol: 'млн' },
        { value: 1e9, symbol: 'G' },
        { value: 1e12, symbol: 'T' },
        { value: 1e15, symbol: 'P' },
        { value: 1e18, symbol: 'E' },
    ];
    const rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
    const item = lookup
        .slice()
        .reverse()
        .find(function (it) {
            return num >= it.value;
        });
    return item ? (num / item.value).toFixed(digits).replace(rx, '$1') + item.symbol : '0';
}

export function generateColors(qty) {
    const colors = [];
    while (colors.length !== qty) {
        const color = Math.floor(Math.random() * 16777215).toString(16);
        if (colors.includes(color)) {
            generateColors(qty - colors.length);
        } else {
            colors.push(`#${color}`);
        }
    }
    return colors;
}

export const setChartScale = value => {
    return value.toLocaleString().replace(/,/g, ' ');
};
