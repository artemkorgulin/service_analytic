import { errorHandler } from '~utils/response.utils';
export default {
    namespaced: true,
    state: () => ({
        marketplaces: [],
        companies: [],
        users: [],
        roles: [],
        company: null,
        transferFlag: false,
    }),
    getters: {
        companiesSelect(state) {
            const companies = JSON.parse(JSON.stringify(state.companies));
            companies.push({
                name: 'Пользователь',
                id: 0,
            });
            return companies;
        },
        isCompanies(state) {
            return state.companies.length > 0;
        },
        rolesAll(state) {
            const roles = JSON.parse(JSON.stringify(state.roles));
            roles.push({
                description: 'Владелец',
                id: 17,
                name: 'company.owner',
                disabled: true,
            });
            return roles;
        },
    },
    actions: {
        async getAllAccounts({ commit }) {
            try {
                let marketplaces = [];
                const {
                    data: { data },
                } = await this.$axios({
                    method: 'GET',
                    url: '/api/v1/get-all-available-accounts',
                });
                marketplaces = data.map(item => ({
                    ...item,
                    img:
                        item.platform_title === 'Ozon'
                            ? '/images/icons/ozon.png'
                            : '/images/icons/wb.png',
                }));
                commit('setCompaniesData', ['marketplaces', marketplaces]);
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async loadCompanies({ commit }) {
            try {
                const topic = '/api/v1/company';
                const {
                    data: { data },
                } = await this.$axios.get(topic);
                commit('setCompaniesData', ['companies', data]);
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async loadRoles({ commit }) {
            try {
                const topic = '/api/v1/company-roles';
                const {
                    data: { data },
                } = await this.$axios.get(topic);
                commit('setCompaniesData', ['roles', data]);
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async transferAccount({ commit }, { accountId, companyToId, companyFromId }) {
            try {
                await this.$axios({
                    method: 'PATCH',
                    url: '/api/v1/transfer-accounts',
                    params: {
                        company_from_id: companyFromId,
                        company_to_id: companyToId,
                        account_id: accountId,
                    },
                });
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async lockUser({ commit }, { id, companyId }) {
            try {
                await this.$axios({
                    method: 'DELETE',
                    url: '/api/v1/user-company',
                    params: {
                        user_id: id,
                        company_id: companyId,
                    },
                });
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },

        async getCompany({ commit }, id) {
            try {
                const {
                    data: { data },
                } = await this.$axios({
                    method: 'GET',
                    url: `/api/v1/company/${id}`,
                });
                const users = data.users.map(item => ({
                    id: item.pivot.user_id,
                    data: item.pivot.created_at,
                    companyId: id,
                    email: item.email,
                    name: item.name,
                    role: item.pivot.roles,
                }));
                commit('setCompaniesData', ['company', data]);
                commit('setCompaniesData', ['users', users]);
            } catch (error) {
                console.error(error);
                errorHandler(error, this.$notify);
            }
        },
    },
    mutations: {
        setCompaniesData(state, value) {
            state[value[0]] = value[1];
        },
    },
};
