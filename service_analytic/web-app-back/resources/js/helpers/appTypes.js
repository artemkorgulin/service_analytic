export const appTypes = {
    isArray: function (value) {
        return Array.isArray(value);
    },

    isBoolean: function (value) {
        return typeof value === 'boolean';
    },

    isDate: function (value) {
        return value instanceof Date;
    },

    isEmpty: function (value) {
        return typeof value === 'undefined'
            || Number.isNaN(value)
            || value === null
            || value === ''
            || value === 0
            || value === false
            || (value.hasOwnProperty('length') && value.length === 0)
            || (Object.keys(value).length === 0 && value.constructor === Object);
    },

    isError: function (value) {
        return value instanceof Error && typeof value.message !== 'undefined';
    },

    isFunction: function (value) {
        return typeof value === 'function';
    },

    isJson: function (str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    },

    isNotEmpty: function (value) {
        return typeof value !== 'undefined'
            && !Number.isNaN(value)
            && value !== null
            && value !== ''
            && value !== 0
            && value !== false
            && !(value.hasOwnProperty('length') && value.length === 0);
    },

    isNumber: function (value) {
        return typeof value === 'number' && isFinite(value);
    },

    isNull: function (value) {
        return value === null;
    },

    isObject: function (data) {
        return Object.prototype.toString.call(data) === '[object Object]'
    },

    isRegExp: function (value) {
        return value && typeof value === 'object' && value.constructor === RegExp;
    },

    isString: function (value) {
        return typeof value === 'string' || value instanceof String;
    },

    isSymbol: function (value) {
        return typeof value === 'symbol';
    },

    isUndefined: function (value) {
        return typeof value === 'undefined';
    },

    jsonToObject: function (data) {
        if (this.isJson(data)) {
            return JSON.parse(data);
        } else if (this.isObject(data) || this.isArray(data)) {
            return data;
        } else {
            return {};
        }
    }
};
