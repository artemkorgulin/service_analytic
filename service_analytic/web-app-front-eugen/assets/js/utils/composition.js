import { unref } from '@nuxtjs/composition-api';
export function unrefObject(payload) {
    return Object.entries(payload).reduce((acc, [key, val]) => {
        acc[key] = unref(val);
        return acc;
    }, {});
}
