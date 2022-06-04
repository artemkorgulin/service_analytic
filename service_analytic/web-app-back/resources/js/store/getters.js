import {appTypes} from "../helpers/appTypes";

export default {
    currentDestroyUrl: state => {
        return state.currentDestroyUrl;
    },
    currentEditElement: state => {
        return state.currentEditElement;
    },
    currentStoreUrl: state => {
        return state.currentStoreUrl;
    },
    currentUpdateUrl: state => {
        return state.currentUpdateUrl;
    },
    currentUser: state => {
        return state.currentUser;
    },
    isGlobalLoader: state => {
        return state.isGlobalLoader;
    },
    isAuthUser: state => {
        return appTypes.isNotEmpty(state.currentUser);
    },
    permission: state => {
        return state.permission;
    },
    getTokenApi: async (state) => {
        const response = await axios
            .post(state.setUrlAdminApi + '/v1/sign-in', {
                email: this.user.email,
                password: this.env.API_PASSWORD
            })

        return response.data.user;
    }

};
