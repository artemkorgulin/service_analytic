// import qs from 'qs';

export default ({ app, $config, $axios, req }, inject) => {
    processAxiosInstance($axios, req, app);

    let endpoint = $config.API_URL;
    if ($config.ENABLE_PROXY && process.client) {
        endpoint = `${location.origin}`;
    }

    $axios.setBaseURL(endpoint);

    const dadata = $axios.create();
    const dadataEndpoint = process.client
        ? `${location.origin}`
        : `${$config.DEVELOPMENT ? 'http' : 'https'}://${$config.APP_HOST}:${$config.APP_PORT}`;

    processAxiosInstance(dadata, req);

    dadata.setBaseURL(dadataEndpoint);

    inject('dadata', dadata);
};

function processAxiosInstance(axios, req, app) {
    /* eslint-disable */
    // let originalReq;
    // const recordableUrls = ['api/vp/v2/wildberries/products', 'api/vp/v2/products'];
    // axios.defaults.paramsSerializer = params => qs.stringify(params, { arrayFormat: 'repeat' });
    axios.defaults.xsrfCookieName = 'csrftoken';
    axios.defaults.xsrfHeaderName = 'X-CSRFToken';
    const headers = req && req.headers ? Object.assign({}, req.headers) : {};
    axios.setHeader('X-Forwarded-Host', headers['x-forwarded-host']);
    axios.setHeader('X-Forwarded-Port', headers['x-forwarded-port']);
    axios.setHeader('X-Forwarded-Proto', headers['x-forwarded-proto']);

    const processListAddress = ['api/vp/v2/products', 'api/vp/v2/wildberries/products'];

    function checkProccessUrl(urlsList, url) {
        if (!Array.isArray(urlsList)) {
            throw new Error('Url list is not an array');
        }

        for (let i = 0; i < urlsList.length; i += 1) {
            const item = urlsList[i];
            const re = new RegExp(item);
            if (re.test(url)) {
                return true;
            }
        }
        return false;
    }

    axios.interceptors.response.use(
        function (response) {
            /* eslint-disable */
            try {
                const {
                    config: { url },
                } = response;

                // const { name: pageName } = app.router.history.current;
                const {
                    getters: { selectedAccId },
                } = app.store;

                if (checkProccessUrl(processListAddress, url)) {
                    localStorage.connectionAttempts = 0;
                    localStorage.lastSuccAccId = selectedAccId;
                }
            } catch {}

            return response;
        },
        async function (error) {
            const {
                config,
                response: { status },
            } = error;

            if (status === 401) {
                logOut();
            }

            return Promise.reject(error);
        }
    );
}

function logOut() {
    document.cookie.split(';').forEach(cookie => {
        const [nameCookie] = cookie.split('=');
        document.cookie = `${nameCookie}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
    });
    localStorage.clear();
    location.reload();
}
