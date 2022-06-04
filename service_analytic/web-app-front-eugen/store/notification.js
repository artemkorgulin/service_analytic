// import User from './index';
function SET_FIELD(state, { value, field }) {
    state[field] = value;
}

function getObjectFromArray(key, array) {
    const newObject = {};

    array.forEach(el => {
        newObject[el[key]] = el;
    });

    return newObject;
}

export default {
    state: () => ({
        notifList: [],
        allNotifList: [],
        notifWindow: false,
        notificationType: {},
        lastPageNotif: null,
    }),
    getters: {},
    actions: {
        async getNotifForJournal(
            { state, commit, dispatch, rootState },
            { page, perPage, active = 1 } = {}
        ) {
            if (page === 1) {
                commit('SET_FIELD', {
                    field: 'allNotifList',
                    value: [],
                });
            }

            try {
                await dispatch('getNotifiTypes');
                const { id: userId } = rootState.auth.user;
                let topic = `/api/event/v1/notifications?user_id=${userId}&is_active=${active}&order_by_desc=1`;

                if (page && perPage) {
                    topic += `&perPage=${perPage}&currentPage=${page}`;
                }

                const {
                    data: { data },
                } = await this.$axios.get(topic);

                commit('SET_FIELD', {
                    field: active === 1 ? 'notifList' : 'allNotifList',
                    value:
                        active === 1
                            ? [...state.notifList, ...data]
                            : [...state.allNotifList, ...data.data],
                });

                if (page && perPage) {
                    commit('SET_FIELD', {
                        field: 'lastPageNotif',
                        value: data.last_page,
                    });
                }
            } catch (error) {
                console.error('Error receiving user notifications', error);
            }
        },
        async getNotifiTypes({ commit }) {
            const topic = '/api/event/v1/notification_types';

            try {
                const {
                    data: { data },
                } = await this.$axios.get(topic);

                commit('SET_TYPE', data);
            } catch (error) {
                console.error('Error getting notification types');
            }
        },
        async readNotif({ state, rootState }) {
            const lastNotifId = state.notifList[0]?.id ?? undefined;
            if (!lastNotifId) {
                return;
            }

            try {
                const { id: userId } = rootState.auth.user;
                const topic = '/api/event/v1/notification_make_read';

                await this.$axios.post(topic, {
                    user_id: userId,
                    notification_id: lastNotifId,
                });
            } catch (error) {
                console.error(error);
            }
        },
    },
    mutations: {
        SET_FIELD,
        SET_TYPE(state, payload) {
            state.notificationType = getObjectFromArray('id', payload);
        },
    },
    namespaced: true,
};
