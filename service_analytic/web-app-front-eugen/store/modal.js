export default {
    namespaced: true,
    state: () => ({
        isModalShow: false,
        drawerAfterEnter: () => ({}),
    }),
    mutations: {
        setModal(state, payload) {
            state.isModalShow = payload;
        },
        setDrawerAfterEnter(state, payload) {
            state.drawerAfterEnter = payload;
        },
    },
};
