import Vue from 'vue';
import Vuex from 'vuex';
import state from './state';
import getters from './getters';
import mutations from './mutations';
import actions from './actions';

// MODULES IMPORT SECTION //
import users from './modules/users';

Vue.use(Vuex);

export default new Vuex.Store({

    state: state,
    getters: getters,
    mutations: mutations,
    actions: actions,
    modules: {
        user: users
    },

});
