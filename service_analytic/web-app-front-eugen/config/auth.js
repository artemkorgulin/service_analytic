export default {
    redirect: {
        login: '/login',
        logout: '/login',
        callback: '/',
        home: '/',
    },
    // login: User will be redirected to this path if login is required.
    // logout: User will be redirected to this path if after logout, current route is protected.
    // home: User will be redirected to this path after login. (rewriteRedirects will rewrite this path)
    // callback: User will be redirected to this path by the identity provider after login. (Should match configured Allowed Callback URLs (or similar setting) in your app/client with the identity provider)
    // Each redirect path can be disabled by setting to false. Also you can disable all redirects by setting redirect to false
    cookie: {
        options: {
            expires: 365,
        },
    },
    localStorage: false,
    // plugins: ['~/config/plugins/auth.js'],
    strategies: {
        custom: {
            scheme: '~/config/auth/strategy.js',
            token: {
                property: 'token',
                global: true,
                maxAge: 60 * 60 * 60 * 24 * 30,
            },
            user: {
                property: 'data.user',
                autoFetch: true,
            },
            endpoints: {
                login: { url: '/api/v1/sign-in', method: 'post' },
                logout: { url: '/api/v1/logout', method: 'post' },
                user: { url: '/api/v1/me', method: 'get' },
            },
        },
    },
};
