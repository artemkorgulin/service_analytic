export default {
    setCurrentDestroyUrl: (state, url) => {
        state.currentDestroyUrl = url;
    },
    setCurrentEditElement: (state, element) => {
        state.currentEditElement = element;
    },
    setCurrentStoreUrl: (state, url) => {
        state.currentStoreUrl = url;
    },
    setCurrentUpdateUrl: (state, url) => {
        state.currentUpdateUrl = url;
    },
    setCurrentUser: (state, user) => {
        state.currentUser = user;
    },
    setSeller: (state, value)=>{
        state.seller =  value;
    },
    setGlobalLoader: (state, value) => {
        return state.globalLoader = value;
    },
    setTokenApi: (state, value) => {
        return state.tokenApi = value;
    },
    setTokenApiV2: (state, value) => {
        return state.tokenV2 = value;
    },
    setUrlAdminApi: (state, value) => {
        return state.urlAdminApi = value;
    },
    setUrlApi: (state, value) => {
        return state.urlApi = value;
    },
    setUrlApiVA: (state, value) => {
        return state.urlApiVA = value;
    },
    setPermission: (state, value) => {
        return state.permission = value;
    }
};
