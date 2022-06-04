import { resolve } from 'path';
import defu from 'defu';
/* eslint-disable no-extra-parens */
const DEFAULT_CONFIGS = {
    types: {
        positive: {
            // icon() {
            //     return 'success';
            // },
            color: 'positive',
            position: 'top-right',
            // timeout: 0,
            actions: [
                {
                    color: 'transparent',
                    icon: true,
                    hover: false,
                    iconName: 'close',
                    small: true,
                },
            ],
        },

        negative: {
            // icon() {
            //     return 'error';
            // },
            color: 'negative',
            position: 'top-right',
            timeout: 0,
            actions: [
                {
                    color: 'transparent',
                    icon: true,
                    hover: false,
                    iconName: 'close',
                    small: true,
                },
            ],
        },

        warning: {
            // icon() {
            //     return 'error';
            // },
            color: 'warning',
            position: 'top-right',
            // timeout: 0,
            actions: [
                {
                    color: 'transparent',
                    icon: true,
                    hover: false,
                    iconName: 'close',
                    small: true,
                },
            ],
        },

        info: {
            // icon() {
            //     return 'notification';
            // },
            type: 'info',
            color: 'info',
            position: 'top-right',
            // timeout: 0,
            actions: [
                {
                    color: 'vice',
                    icon: true,
                    iconName: 'close',
                    small: true,
                },
            ],
        },

        ongoing: {
            group: false,
            timeout: 0,
            spinner: true,
            color: 'grey-8',
        },
    },
};
export default async function module(_moduleOptions) {
    const { nuxt } = this;
    const moduleOptions = {
        ...nuxt.options.notify,
        ..._moduleOptions,
        ...(nuxt.options.runtimeConfig && nuxt.options.runtimeConfig.notify),
    };

    const options = defu(moduleOptions, DEFAULT_CONFIGS);
    // Transpile defu (IE11)
    if (nuxt.options.build.transpile) {
        nuxt.options.build.transpile.push(({ isClient }) => isClient && 'defu');
    }
    this.options.alias['~notify'] = resolve(__dirname);
    this.addPlugin({
        src: resolve(__dirname, './plugin.js'),
        fileName: 'notify.js',
        options,
    });
}
