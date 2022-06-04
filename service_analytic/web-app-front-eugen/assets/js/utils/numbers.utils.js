export const leadingZero = num => num < 10 ? `0${num}` : num;

export function splitThousands(val) {
    if (isNaN(val)) {
        return val;
    }

    val = Math.floor(Number(val));
    const prefix = val < 0 ? '-' : '';

    return (
        prefix +
        val
            .toString()
            .replace(/\D/g, '')
            .replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
    );
}

export function roundToMillions(num, accuracy = 1) {
    if (num === undefined || num === null) {
        return '';
    }

    return (Number(num) / 1000000).toFixed(accuracy);
}

export function onlyNumbers(val) {
    return val.toString().replace(/\D/g, '');
}

export function onlyLetters(val) {
    return val.toString().replace(/[^a-zA-Z ]+/g, '');
}

export function prettyPhone(rawPhoneNumber) {
    return onlyNumbers(rawPhoneNumber).replace(
        /(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/,
        '+$1 ($2) $3-$4-$5'
    );
}
/* eslint-disable guard-for-in */
export function toRoman(num) {
    const romanNumbers = {
        M: 1000,
        CM: 900,
        D: 500,
        CD: 400,
        C: 100,
        XC: 90,
        L: 50,
        XL: 40,
        X: 10,
        IX: 9,
        V: 5,
        IV: 4,
        I: 1,
    };
    let roman = '';
    for (const i in romanNumbers) {
        while (num >= romanNumbers[i]) {
            roman += i;
            num -= romanNumbers[i];
        }
    }
    return roman;
}

export function quaterToRoman(num) {
    if (!Number.isInteger(num)) {
        console.warn('[UUtils/Number/quaterToRoman] Аргумент "num" должен быть Number: ', num);
        return '';
    }

    switch (num) {
        case 1:
            return ' I';
        case 2:
            return ' II';
        case 3:
            return ' III';
        default:
            return 'IV';
    }
}

export function currency(value) {
    if (typeof value === 'undefined' || value === null) {
        console.warn('[Utils/Number/currency] value is required');
        return '';
    }
    return value.toLocaleString('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    });
}

export function roundNumber(num, scale) {
    if (Math.round(num) != num) {
        if (Math.pow(0.1, scale) > num) {
            return 0;
        }
        const sign = Math.sign(num);
        const arr = String(Math.abs(num)).split('.');
        if (arr.length > 1) {
            if (arr[1].length > scale) {
                const integ = Number(arr[0]) * Math.pow(10, scale);
                let dec = integ + (Number(arr[1].slice(0, scale)) + Math.pow(10, scale));
                const proc = Number(arr[1].slice(scale, scale + 1));
                if (proc >= 5) {
                    dec += 1;
                }
                dec = sign * (dec - Math.pow(10, scale)) / Math.pow(10, scale);
                return dec;
            }
        }
    }
    return num;
}
