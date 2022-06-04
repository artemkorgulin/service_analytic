/* eslint-disable no-extra-parens */
export default env => ({
    ...(env.ENABLE_PROXY
        ? {
              '/api': {
                  target: env.API_URL,
                  // cookieDomainRewrite: {
                  //     '*': '',
                  // },
              },
          }
        : null),
    '/suggestions': {
        target: 'https://suggestions.dadata.ru',
        // changeOrigin: true,
        // cookieDomainRewrite: {
        //     '*': '',
        // },
    },
});
