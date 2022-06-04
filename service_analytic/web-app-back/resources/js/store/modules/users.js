import {appTypes} from "../../helpers/appTypes";
import {appFunctions} from "../../helpers/appFunctions";

const state = {
    users: {}
};

const getters = {
    users: state => {
        return state.users;
    }
};

const mutations = {
    addUsers: (state, data) => {
        let newUsers = {};

        if (appTypes.isNotEmpty(data)) {
            appFunctions.forEach(data, function (value, id) {
                if (appTypes.isEmpty(state.users[id])) {
                    newUsers[id] = value;
                }
            });
        }

        if (appTypes.isNotEmpty(newUsers)) {
            state.users = Object.assign({},  state.users, newUsers);
        }
    }
};

const actions = {

};

export default {
    state,
    getters,
    mutations,
    actions,
};
