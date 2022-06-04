// const hex = /^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/;
// const hexa = /^#[0-9a-fA-F]{4}([0-9a-fA-F]{4})?$/;
// const hexOrHexa = /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/;
// const rgb = /^rgb\(((0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),){2}(0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5])\)$/;
// const rgba = /^rgba\(((0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),){2}(0|[1-9][\d]?|1[\d]{0,2}|2[\d]?|2[0-4][\d]|25[0-5]),(0|0\.\d+[1-9]|0\.[1-9]+|1)\)$/;

// export const testPatterns = {
// date: v => /^-?[\d]+\/[0-1]\d\/[0-3]\d$/.test(v),
// time: v => /^([0-1]?\d|2[0-3]):[0-5]\d$/.test(v),
// fulltime: v => /^([0-1]?\d|2[0-3]):[0-5]\d:[0-5]\d$/.test(v),
// timeOrFulltime: v => /^([0-1]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/.test(v),

// hexColor: v => hex.test(v),
// hexaColor: v => hexa.test(v),
// hexOrHexaColor: v => hexOrHexa.test(v),

// rgbColor: v => rgb.test(v),
// rgbaColor: v => rgba.test(v),
// rgbOrRgbaColor: v => rgb.test(v) || rgba.test(v),

// hexOrRgbColor: v => hex.test(v) || rgb.test(v),
// hexaOrRgbaColor: v => hexa.test(v) || rgba.test(v),
// anyColor: v => hexOrHexa.test(v) || rgb.test(v) || rgba.test(v),
//     cyrillicAlpha: v => /^([а-яА-ЯЁё]*?)$/.test(v),
//     passportNum: v => /^(\d{2}\s\d{2}\s{1}\d{6})?$/.test(v),
//     birthDate: v => /^(0[1-9]|[12]\d|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/.test(v),
// };

/* eslint-disable no-control-regex */
const emailRegex =
    /^(?:[A-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[A-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|[\x01-\x09\x0B\x0C\x0E-\x7F])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9]{2,}(?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(?:25[0-5]|2[0-4]\d|[01]?\d\d?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21-\x5A\x53-\x7F]|\\[\x01-\x09\x0B\x0C\x0E-\x7F])+)\])$/i;

export const cyrillicAlpha = v => /^([а-яА-ЯЁё]*?)$/.test(v);
export const passportNum = v => /^(\d{2}\s\d{2}\s{1}\d{6})?$/.test(v);
export const birthDate = v =>
    /^(0[1-9]|[12]\d|3[01])[- /.](0[1-9]|1[012])[- /.](19|20)\d\d$/.test(v);
export const phone = v => v && v.replace(/\D/g, '').length >= 11;
export const phoneIfAny = v => !v || v.replace(/\D/g, '').length >= 11;
export const email = v => emailRegex.test(v);
export const minLength = num => v => v && v.length >= num;
export const minLengthIfAny = num => v => !v || v.length >= num;
export const notLessThen = num => v => Number(v) >= num;
export const required = val => {
    if (val === undefined || val === null) {
        return false;
    }

    return String(val).replace(/\s/g, '').length > 0;
};
export const numeric = v => /^\d*(\.\d+)?$/.test(v);
export const maxLength = num => v => v && v.length <= num;
