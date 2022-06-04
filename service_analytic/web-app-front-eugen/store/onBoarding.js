import { setField } from '~/assets/js/utils/helpers';

export default {
    namespaced: true,
    state: () => ({
        activeElements: {},
        elements: [],
        activateOb: false,
        isOnboardActive: false,
    }),
    mutations: {
        setField,
        setActiveElements(state, elements) {
            state.elements = elements;
        },
        setOnboardActive(state, value) {
            state.isOnboardActive = value;
        },
        setFirstStepOnPage(state, name) {
            state.elements = state.activeElements[name];
        },
        setFirstStep(state, { field, value }) {
            state.activeElements[field] = value;
        },
    },
    actions: {},
    getters: {
        isOnboardActive: state => state.isOnboardActive,
    },
};
