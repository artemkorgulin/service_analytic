<template>
    <div>
        <div class="row">
            <div class="card col" style="padding: 10px; margin:0 30px 30px 30px">
                <div class="row justify-content-center">
                    <h3 class="card-title text-center">Редактирование нового пользователя</h3>
                </div>

                <div class="row">
                    <div class="col">
                        <label>ФИО</label>
                        <input :disabled="!permissions['user.edit']" type="text" v-on:input="changeUser('name')"
                               class="form-control" placeholder="Фамилия Имя Отчество" aria-label="фио" v-model="name">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label>email</label>
                        <input :disabled="!permissions['user.edit']" type="text" v-on:input="changeUser('email')"
                               class="form-control" placeholder="электронная почта" aria-label="email" v-model="email">
                    </div>

                </div>
                <div class="row">
                    <div class="col">
                        <label>телефон</label>
                        <input type="text" :disabled="!permissions['user.edit']" v-on:input="changeUser('phone')"
                               class="form-control" placeholder="телефон пользователя" aria-label="phone"
                               v-model="phone">
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label>ИНН</label>
                        <input type="text" :disabled="!permissions['user.edit']" v-on:input="changeUser('inn')"
                               class="form-control" placeholder="телефон пользователя" aria-label="phone" v-model="inn">
                    </div>
                </div>

                <div class="form-check form-switch" style="margin-top: 15px">
                    <input :disabled="!permissions['user.edit']" v-on:change="changeActiveUser" class="form-check-input"
                           v-model="isactive" type="checkbox" id="flexSwitchCheckDefault">
                    <label class="form-check-label" for="flexSwitchCheckDefault">активный/неактивный</label>
                </div>

            </div>
            <div class="card col" style="padding: 10px; margin:0 30px 30px 30px">
                <div class="row justify-content-center">
                    <h3 class="card-title text-center">Смена пароля</h3>
                </div>

                <div class="row justify-content-center">
                    <div class="col">
                        <label>пароль</label>
                        <input :disabled="!permissions['user.edit']" v-on:input="changePasswordField('password')"
                               type="text" class="form-control" placeholder="пароль" aria-label="пароль"
                               v-model="password">
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col">
                        <label>повторить пароль</label>
                        <input :disabled="!permissions['user.edit']"
                               v-on:input="changePasswordField('password_confirmation')" type="text"
                               class="form-control" placeholder="подтвердить пароль" aria-label="подтвердить"
                               v-model="password_confirmation">
                    </div>

                </div>
            </div>
            <div class="card col" style="padding: 10px; margin:0 30px 30px 30px">
                <div class="row justify-content-center">
                    <h3 class="card-title text-center">Редактировать роли для пользователя</h3>
                </div>
                <div class="">

                    <label class="typo__label">роли пользователя</label>
                    <multiselect :disabled="!permissions['user.edit']" @input="changeRole" v-model="role_user"
                                 :options="roles" :multiple="true" :close-on-select="true" :clear-on-select="false"
                                 :preserve-search="true" placeholder="Pick some" label="name" track-by="name"
                                 :preselect-first="true">
                    </multiselect>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="card col" style="padding: 10px; margin:0 30px 30px 30px">
                <div class="row justify-content-center">
                    <h3 class="card-title text-center">Подключение Маркетплейсов</h3>
                </div>
                <div class="">
                    <label class="typo__label">Маркетплейы</label>
                    <multiselect :disabled="true" v-model="platforms_user" :options="platforms" :multiple="true"
                                 :close-on-select="false" :clear-on-select="false" :preserve-search="true"
                                 placeholder="Pick some" label="title" track-by="title" :preselect-first="true">
                    </multiselect>
                </div>
            </div>
            <div class="card col" style="padding: 10px; margin:0 30px 30px 30px">
                <div class="row justify-content-center">
                    <h3 class="card-title text-center">Настройка системы Уведомлений</h3>
                </div>
                <div class="row justify-content-center select2">
                    здесь будет настройка
                </div>
            </div>
            <div class="card col" style="padding: 10px; margin:0 30px 30px 30px">
                <div class="row justify-content-center">
                    <h3 class="card-title text-center">Настройка тарифов</h3>
                </div>
                <div class="">
                    <label class="typo__label">тарифы</label>
                    <multiselect :disabled="!permissions['tariff.edit']" @input="changeUserTariff" v-model="tariff"
                                 :options="tariffs" :multiple="false" :close-on-select="true" :clear-on-select="false"
                                 :preserve-search="true" placeholder="Pick some" label="description"
                                 track-by="description" :preselect-first="true">
                    </multiselect>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import state from "../../store/state";
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

export default {
    name: "UserEdit",
    components: {
        Multiselect
    },
    props: {
        id: null
    },
    data: function () {
        return {
            name: null,
            email: null,
            phone: null,
            inn: null,
            isactive: null,
            password: null,
            password_confirmation: null,
            role_user: [],
            value: [],
            roles: [],
            platforms_user: null,
            platforms: [],
            test: 'test',
            oz: {},
            wb: {},
            account_wb: null,
            account_oz: null,
            tariffs: [],
            tariff: {},
            changeUserFields: {},
            changePasswordFields: {},
            permissions: []
        }
    },
    beforeCreate() {

    },
    methods: {
        showError: function (err) {
            this.$toast.error(err, {
                position: "top-right",
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: true,
                closeButton: "button",
                icon: true,
                rtl: false
            });
        },
        showSuccess: function (message) {

            this.$toast.success(message, {
                position: "top-right",
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: true,
                closeButton: "button",
                icon: true,
                rtl: false
            });
        },
        catch: function (err) {
            console.log('Catch ', err);
            if (err.response) {
                if (typeof err.response.data.error === 'string') {
                    this.showError(err.response.data.error);
                } else {
                    this.showError(err.response.data.error.message);
                    if (err.response.data.error.advanced) {
                        for (const error of err.response.data.error.advanced) {
                            for (let item in error) {
                                this.showError(error[item]);
                            }
                        }
                    }
                }
            } else if (err.request) {
                this.showError(String(err.request));
            } else {
                this.showError(String(err));
            }
        },
        getGoodsUserOz: function (page = 1) {
            let url;
            if (this.account_oz)
                url = state.urlApi + '/v1/get-goods-user-oz/' + this.$route.params.id + '/' + this.account_oz + '?page=' + page;
            else
                url = state.urlApi + '/v1/get-goods-user-oz/' + this.$route.params.id + '?page=' + page;

            this.$http.get(url, {headers: {Authorization: state.tokenApi}})
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.oz = data;
                })
                .catch(this.catch);
        },
        getGoodsUserWb: function (page = 1) {
            let url;
            if (this.account_wb)
                url = state.urlApi + '/v1/get-goods-user-wb/' + this.$route.params.id + '/' + this.account_wb + '?page=' + page;
            else
                url = state.urlApi + '/v1/get-goods-user-wb/' + this.$route.params.id + '?page=' + page;

            this.$http.get(url, {headers: {Authorization: state.tokenApi}})
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.wb = data;
                })
                .catch(this.catch);
        },
        getUserTariff: function () {
            axios.get(state.urlAdminApi + '/tariffs', {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.tariffs = res.data.data;
                })
                .catch(this.catch);
        },
        getPlatforms: function () {
            axios.get(state.urlApi + '/v1/get-platforms', {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.platforms = res.data;
                })
                .catch(this.catch);
        },
        getRoles: function () {
            axios.get(state.urlAdminApi + '/get-roles', {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.roles = res.data;
                })
                .catch(this.catch);
        },
        getUser: function () {
            axios.get(state.urlAdminApi + '/get-user/' + this.$route.params.id, {
                headers: {Authorization: state.tokenApi}
            })
                .then((res) => {
                    if (!Object.keys(res.data).length) {
                        this.showError('Не найден пользователь');
                        this.$router.push({name: 'Users'});
                    }
                    this.role_user = res.data.roles;
                    this.name = res.data.name;
                    this.email = res.data.email;
                    this.phone = res.data.phone;
                    this.isactive = res.data.isactive;
                    this.inn = res.data.inn;
                    this.platforms_user = res.data.platforms;
                    this.tariff = res.data.tariff;
                })
                .catch(this.catch);
        },
        changeActiveUser: function () {
            axios.patch(state.urlAdminApi + '/change-active-user/' + this.$route.params.id, {isactive: this.isactive}, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data)
                })
                .catch(this.catch);
        },
        editUser: function () {
            axios.patch(state.urlAdminApi + '/edit-user/' + this.$route.params.id,
                {
                    name: this.name,
                    email: this.email,
                    phone: this.phone,
                    inn: this.inn,
                    changeUserFields: this.changeUserFields
                },
                {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data)
                    this.changeUserFields = {};
                })
                .catch(this.catch);
        },
        changePasswordUser: function () {
            axios.patch(state.urlAdminApi + '/change-password-user/' + this.$route.params.id,
                {
                    password: this.password,
                    password_confirmation: this.password_confirmation
                },
                {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data);
                    this.changePasswordFields = {};
                })
                .catch(this.catch);
        },
        changeUser: function (value) {
            this.changeUserFields[value] = this[value];
        },
        changePasswordField: function (value) {
            this.changePasswordFields[value] = this[value];
        },
        isEmpty: function (value) {
            return (
                value === null || // check for null
                value === undefined || // check for undefined
                value === '' || // check for empty string
                (Array.isArray(value) && value.length === 0) || // check for empty array
                (typeof value === 'object' && Object.keys(value).length === 0) // check for empty object
            );
        },
        changeRole: function () {

            let roles = [];
            for (const role of this.role_user) {
                roles.push(role.id);
            }
            axios.patch(state.urlAdminApi + '/change-roles-user/' + this.$route.params.id,
                {roles: roles,},
                {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data);
                })
                .catch(this.catch);
        },
        changeUserTariff: function () {
            axios.defaults.withCredentials = true;
            axios.patch(state.urlApi + '/v1/change-user-tariff/' + this.$route.params.id,
                {tariff: this.tariff.id},
                {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data);
                })
                .catch(this.catch);

        },
        getPermission: function () {
            axios.get(state.urlApi + '/v1/get-user-permissions/' + state.currentUser.id, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    for (const item in res.data) {
                        if (res.data.hasOwnProperty(item)) {
                            this.permissions[res.data[item].name] = true;
                        }
                    }
                })
                .catch(this.catch);
        }
    },
    created() {
        window.navBar.$on('saveUser', () => {
            if (!this.isEmpty(this.changeUserFields))
                this.editUser();
            if (!this.isEmpty(this.changePasswordFields))
                this.changePasswordUser();
        });
        this.getPermission()
        this.getUser();
        this.getRoles();
        this.getPlatforms();
        this.getGoodsUserOz();
        this.getGoodsUserWb();
        this.getUserTariff();
    }
}
</script>

<style scoped>
body {
    font-family: 'Source Sans Pro', 'Helvetica Neue', Arial, sans-serif;
    text-rendering: optimizelegibility;
    -moz-osx-font-smoothing: grayscale;
    -moz-text-size-adjust: none;
}

h1, .muted {
    color: #2c3e5099;
}

h1 {
    font-size: 26px;
    font-weight: 600;
}

.select2 {
    max-width: 30em;
    margin: 1em auto;
}
</style>
