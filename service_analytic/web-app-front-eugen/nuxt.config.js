import { resolve } from 'path';
import { isSet } from './assets/js/utils/helpers';
import plugins from './config/plugins';
import modules from './config/modules';
import proxyConfig from './config/proxy';
import authConfig from './config/auth';

/*  no-extra-parens */
function getEnvVar(name, defaultValue = false) {
    if (!name) {
        console.warn('[getEnvVar] no env name provided');
        return false;
    }
    try {
        return isSet(process.env[name]) ? JSON.parse(process.env[name]) : defaultValue;
    } catch (error) {
        console.log('🚀 ~ file: nuxt.config.js ~ line 17 ~ getEnvVar ~ error', error);
        return process.env[name] || defaultValue;
    }
}
export default () => {
    const isDev = process.env.NODE_ENV === 'development';
    const envs = {
        API_URL: process.env.API_URL || 'https://back.dev.sellerexpert.ru',
        DEVELOPMENT: isDev,
        ENABLE_PROXY: getEnvVar('ENABLE_PROXY', true),
        ENABLE_WARNINGS: getEnvVar('ENABLE_WARNINGS'),
        ENABLE_YANDEX_METRIKA: getEnvVar('ENABLE_YANDEX_METRIKA'),
        ENABLE_SENTRY: getEnvVar('ENABLE_SENTRY'),
        SENTRY_DSN:
            process.env.SENTRY_DSN ||
            'https://adcc9a7e6a494b65aaa5717c52586668@o1001141.ingest.sentry.io/5960646',
        APP_PORT: process.env.FRONT_PORT || 8080,
        APP_HOST: process.env.FRONT_HOST || 0,
        WS_EVENT_URL: process.env.WS_EVENT_URL,
        REG_USER_COUNTER_TOKEN: process.env.REG_USER_COUNTER_TOKEN,
        AG_GRID_KEY: process.env.AG_GRID_KEY,
        DISABLED_MENU_ITEMS: process.env.DISABLED_MENU_ITEMS,
        ENABLE_GTM: process.env.ENABLE_GTM,
        SERVER_TYPE: process.env.SERVER_TYPE
    };
    console.log('🚀 ~ file: nuxt.config.js ~ line 13 ~ envs', envs);
    return {
        telemetry: false,
        alias: {
            '~use': resolve(__dirname, './assets/js/use'),
            '~directives': resolve(__dirname, './assets/js/directives'),
            '~utils': resolve(__dirname, './assets/js/utils'),
            '~mixins': resolve(__dirname, './assets/js/mixins'),
        },
        // Server settings
        server: {
            port: envs.APP_PORT,
            host: envs.APP_HOST,
        },

        router: {
            middleware: 'authLink',
        },

        publicRuntimeConfig: {
            ...envs,
        },
        // Global environments
        env: {
            ...envs,
        },

        // pageTransition: {
        //     name: 'fade',
        //     mode: 'out-in',
        // },

        // Customize the progress-bar color
        loading: {
            color: '#710bff',
            failedColor: 'error',
        },
        ssr: false,
        // Global page headers: https://go.nuxtjs.dev/config-head
        head: {
            titleTemplate: '%s - SellerExpert',
            title: 'SellerExpert',
            htmlAttrs: {
                lang: 'ru',
            },
            meta: [
                { charset: 'utf-8' },
                { name: 'viewport', content: 'width=device-width, initial-scale=1' },
                { hid: 'description', name: 'description', content: '' },
                { name: 'format-detection', content: 'telephone=no' },
            ],
            link: [{ rel: 'icon', type: 'image/x-icon', href: '/favicon.ico' }],
            // script: [{ src: 'https://cdn.experrto.io/client/experrto.js' }],
        },

        // Global CSS: https://go.nuxtjs.dev/config-css
        css: ['~/assets/scss/bundle.scss'],

        // Plugins to run before rendering page: https://go.nuxtjs.dev/config-plugins
        plugins,

        // Auto import components: https://go.nuxtjs.dev/config-components
        components: [
            {
                path: '~/components',
                pathPrefix: false,
                extensions: ['vue'],
                pattern: '**/*.vue',
            },
        ],

        // Modules for dev and build (recommended): https://go.nuxtjs.dev/config-modules
        buildModules: [
            // https://go.nuxtjs.dev/eslint
            '@nuxtjs/eslint-module',
            // https://go.nuxtjs.dev/stylelint
            '@nuxtjs/stylelint-module',
            // https://go.nuxtjs.dev/vuetify
            '@nuxtjs/vuetify',
            // https://github.com/nuxt-community/style-resources-module
            '@nuxtjs/style-resources',
            // https://github.com/nuxt-community/google-fonts-module
            // '@nuxtjs/google-fonts',
            // https://github.com/nuxt-community/router-extras-module
            '@nuxtjs/router-extras',
            // https://github.com/nuxt-community/svg-sprite-module
            '@nuxtjs/svg-sprite',
            // https://github.com/nuxt-community/device-module
            '@nuxtjs/device',
            // https://composition-api.nuxtjs.org/
            '@nuxtjs/composition-api/module',
            // https://github.com/nuxt-community/laravel-echo-module
            '@nuxtjs/laravel-echo',
        ],
        // watchers: {
        //     webpack: {
        //     ignored: /node_modules/,
        //       aggregateTimeout: 300,
        //       poll: 1000
        //     }
        //   },
        // Modules: https://go.nuxtjs.dev/config-modules
        modules: [
            ...modules,
            // https://go.nuxtjs.dev/axios
            '@nuxtjs/axios',
            // https://github.com/nuxt-community/proxy-module
            '@nuxtjs/proxy',
            // https://auth.nuxtjs.org
            '@nuxtjs/auth-next',
            // https://www.npmjs.com/package/nuxt-vuex-router-sync
            'nuxt-vuex-router-sync',
            // '~/config/modules/notification',
            // https://www.npmjs.com/package/@nuxtjs/yandex-metrika
            ...envs.ENABLE_YANDEX_METRIKA ? ['@nuxtjs/yandex-metrika'] : [],
            // https://sentry.nuxtjs.org/
            ...envs.ENABLE_SENTRY ? ['@nuxtjs/sentry'] : [],
            // https://github.com/LinusBorg/portal-vue
            'portal-vue/nuxt',
            // https://www.npmjs.com/package/cookie-universal-nuxt
            'cookie-universal-nuxt',
        ],
        sentry: {
            dsn: process.env.SENTRY_DSN || false,
            // Additional Module Options go here
            // https://sentry.nuxtjs.org/sentry/options
            config: {
                // Add native Sentry config here
                // https://docs.sentry.io/platforms/javascript/guides/vue/configuration/options/
                // debug: false,
                environment: isDev ? 'development' : 'production',
            },
        },
        // echo: {
        //     broadcaster: 'socket.io',
        //     host: 'ws://event.dev.sellerexpert.ru/',
        //     disableStats: true,
        //     // authModule: true,
        //     connectOnLogin: true,
        //     disconnectOnLogout: true,
        // },
        yandexMetrika: {
            id: '84936733',
            webvisor: true,
            clickmap: true,
            useCDN: false,
            trackLinks: true,
            accurateTrackBounce: true,
            ecommerce: 'dataLayer',
        },
        // Axios module configuration: https://go.nuxtjs.dev/config-axios
        axios: {
            progress: false,
            credentials: true,
        },
        proxy: proxyConfig(envs),
        auth: authConfig,
        styleResources: {
            scss: ['~/assets/scss/shared/_variables.scss', '~/assets/scss/shared/*.scss'],
        },
        // Vuetify module configuration: https://go.nuxtjs.dev/config-vuetify
        vuetify: {
            // https://github.com/vuetifyjs/vuetify/tree/master/packages/vuetify/src/styles/settings
            customVariables: ['~/assets/variables.scss'],
            treeShake: true,
            options: {
                customProperties: true,
            },
            defaultAssets: false,
            optionsPath: './config/vuetify.js',
        },
        googleFonts: {
            families: {
                Manrope: [200, 300, 400, 500, 600, 700, 800],
            },
        },
        svgSprite: {
            input: '~/assets/sprite/svg/',
        },
        eslint: {
            cache: false,
        },
        // Build Configuration: https://go.nuxtjs.dev/config-build
        build: {
            loaders: {
                vue: {
                    prettify: false,
                },
            },
            // transpile: ['vuetify'],
            publicPath: '/n/',

            analyze: isDev,

            // Set libraries to transpile by babel
            transpile: !isDev && [],

            // You can extend webpack config here
            babel: {},

            // Terser settings
            terser: !isDev && {
                terserOptions: {
                    mangle: {
                        safari10: true,
                    },
                },
            },

            // Postcss settings
            postcss: {
                // Add plugin names as key and arguments as value
                // Install them before as dependencies with npm or yarn
                preset: {
                    // Change the postcss-preset-env settings
                    autoprefixer: {
                        grid: false,
                    },
                },
            },

            extend(config, ctx) {
                // Fixes npm packages that depend on `fs` module
                config.node = {
                    fs: 'empty',
                };
            },

            // optimization: {
            //     splitChunks: {
            //         name: true,
            //     },
            // },
            //
            // filenames: {
            //     app: () => 'js/[name].[hash].js',
            //     chunk: () => 'js/[name].[hash].js',
            //     css: () => 'css/[name].[hash].css',
            // },
        },
    };
};
