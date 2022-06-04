import {appTypes} from "../helpers/appTypes";

export default {
    showGlobalLoader: (context, loaderParams) => {
        if (appTypes.isEmpty(loaderParams)) {
            loaderParams = {
                container: null,
                canCancel: false,
                loader: 'dots',
                color: '#9561e2'
            };
        }

        let loader = window.Vue.$loading.show(loaderParams);
        context.commit('setGlobalLoader', loader);
    },
    hideGlobalLoader: (context) => {
        context.state.globalLoader.hide();
    }
};
