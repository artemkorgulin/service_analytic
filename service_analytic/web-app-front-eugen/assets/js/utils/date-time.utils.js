import { leadingZero } from './numbers.utils.js';

export const dayByIndex = (index, short) => {
    const fullList = [
        'Понедельник',
        'Вторник',
        'Среда',
        'Четверг',
        'Пятница',
        'Суббота',
        'Воскресение',
    ];

    const shortList = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    return short ? shortList[index] : fullList[index];
};

export const monthByIndex = (index, short, plural) => {
    const fullList = [
        'Январь',
        'Февраль',
        'Март',
        'Апрель',
        'Май',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь',
    ];

    const pluralList = [
        'Января',
        'Февраля',
        'Марта',
        'Апреля',
        'Мая',
        'Июня',
        'Июля',
        'Августа',
        'Сентября',
        'Октября',
        'Ноября',
        'Декабря',
    ];

    const shortList = [
        'Янв',
        'Фев',
        'Мар',
        'Апр',
        'Май',
        'Июн',
        'Июл',
        'Авг',
        'Сен',
        'Окт',
        'Ноя',
        'Дек',
    ];

    if (short) {
        return shortList[index];
    }

    return plural ? pluralList[index] : fullList[index];
};

export const isValidDate = date => !isNaN(Date.parse(date));

export const formatDateTime = (date, pattern) => {
    if (!date) {
        // console.warn('[Utils formatDateTime] date is required');
        return '';
    }
    const symbols = pattern.match(/[$][a-zA-Z]/gm);
    if (!symbols?.length) {
        console.warn(
            '[Utils formatDateTime] pattern must be valid(for example $d.$m.$y contain: $y - year, $d - day, $m - month)'
        );
        return '';
    }
    const d = isValidDate(date) ? new Date(date) : new Date();

    const params = {
        // Date
        $d: leadingZero(d.getDate()), // Day of the month, 2 digits with leading zeros. // '01' to '31'
        $j: d.getDate(), // Day of the month without leading zeros. // '1' to '31'

        // Days of the week
        $D: dayByIndex(d.getDay(), true), // Day of the week, textual, 3 letters. // 'Пн', 'Вт'
        $l: dayByIndex(d.getDay()), // Day of the week, textual, long. // 'Пятница'

        // Month
        $m: leadingZero(d.getMonth() + 1), // Month, 2 digits with leading zeros. // '01' to '12'
        $n: d.getMonth() + 1, // Month without leading zeros. // '1' to '12'
        $M: monthByIndex(d.getMonth(), true), // Month, textual, 3 letters. // 'Янв'
        $b: monthByIndex(d.getMonth(), true).toLowerCase(), // Month, textual, 3 letters, lowercase. // 'янв'
        $F: monthByIndex(d.getMonth()), // Month, textual, long. // 'Январь'
        $E: monthByIndex(d.getMonth(), false, true), // Month, plural, long. // 'Января'
        $e: monthByIndex(d.getMonth(), false, true).toLowerCase(), // Month, plural, long, lowercase // 'января'

        // Year
        $y: d.getFullYear(), // Year, 4 digits. // 1993

        // Time
        // Hours
        $g: Math.floor(d.getHours() / 2), // Hour, 12-hour format without leading zeros. // '1' to '12'
        $G: d.getHours(), // Hour, 24-hour format without leading zeros. // '0' to '23'
        $h: leadingZero(Math.floor(d.getHours() / 2)), // Hour, 12-hour format. // '01' to '12'
        $H: leadingZero(d.getHours()), // Hour, 24-hour format. // '01' to '23'

        // Minutes
        $i: d.getMinutes(), // Minutes, without leading zeros. // '1' to '59'
        $I: leadingZero(d.getMinutes()), // Minutes. // '01' to '59'

        // Seconds
        $s: d.getSeconds(), // Seconds, without leading zeros. // '1' to '59'
        $S: leadingZero(d.getSeconds()), // Seconds. // '1' to '59'
    };

    let result = pattern;

    symbols.forEach(s => {
        result = result.replace(s, params[s]);
    });

    return result;
};

export function normalizeTime(targetTime = new Date(), timeZoneFromDB = '+3.00') {
    const tzDifference = timeZoneFromDB * 60 + targetTime.getTimezoneOffset();
    return new Date(targetTime.getTime() + tzDifference * 1000);
}

function createUTCDate(year, month = 0, day = 1) {
    let date;
    if (year < 100 && year >= 0) {
        date = new Date(Date.UTC(year, month, day));
        if (isFinite(date.getUTCFullYear())) {
            date.setUTCFullYear(year);
        }
    } else {
        date = new Date(Date.UTC(year, month, day));
    }

    return date;
}

function firstWeekOffset(year, firstDayOfWeek, firstDayOfYear) {
    const firstWeekDayInFirstWeek = 7 + firstDayOfWeek - firstDayOfYear;
    const firstWeekDayOfYear =
        (7 + createUTCDate(year, 0, firstWeekDayInFirstWeek).getUTCDay() - firstDayOfWeek) % 7;

    return -firstWeekDayOfYear + firstWeekDayInFirstWeek - 1;
}

function dayOfYear(year, month, day, firstDayOfWeek) {
    let dayOfYear = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334][month];
    if (month > 1 && isLeapYear(year)) {
        dayOfYear++;
    }

    return dayOfYear + day;
}

function weeksInYear(year, firstDayOfWeek, firstDayOfYear) {
    const weekOffset = firstWeekOffset(year, firstDayOfWeek, firstDayOfYear);
    const weekOffsetNext = firstWeekOffset(year + 1, firstDayOfWeek, firstDayOfYear);
    const daysInYear = isLeapYear(year) ? 366 : 365;

    return (daysInYear - weekOffset + weekOffsetNext) / 7;
}

export function weekNumber(year, month, day, firstDayOfWeek, localeFirstDayOfYear) {
    const weekOffset = firstWeekOffset(year, firstDayOfWeek, localeFirstDayOfYear);
    const week = Math.ceil((dayOfYear(year, month, day, firstDayOfWeek) - weekOffset) / 7);

    if (week < 1) {
        return week + weeksInYear(year - 1, firstDayOfWeek, localeFirstDayOfYear);
    } else if (week > weeksInYear(year, firstDayOfWeek, localeFirstDayOfYear)) {
        return week - weeksInYear(year, firstDayOfWeek, localeFirstDayOfYear);
    } else {
        return week;
    }
}

export function isLeapYear(year) {
    return year % 4 === 0 && year % 100 !== 0 || year % 400 === 0;
}

export function theDayOfYear(date) {
    return Math.floor((date - new Date(date.getFullYear(), 0, 0)) / (1000 * 60 * 60 * 24));
}
