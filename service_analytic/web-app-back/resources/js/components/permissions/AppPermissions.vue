<template>
    <div>
        <div class="row justify-content-center">
            <div>
                <h3 class="col-md-11 card-title text-center">
                    Права на роль <span v-if="role['id']">{{ role.id }} - {{ role.name }}</span>
                </h3>
            </div>
        </div>
        <table class="table table-striped table-hover" v-if="permissions[0]">
            <thead>
            <tr>
                <td>#</td>
                <td>Название</td>
                <td>Описание</td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <tr v-for="permission of permissions">
                <td>{{ permission.id }}</td>
                <td>{{ permission.name }}</td>
                <td>{{ permission.description }}</td>
                <td><input v-on:change="changePermission" v-model="p_user[permission.name]" type="checkbox"/></td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import state from "../../store/state";
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
export default {
    name: "AppPermissions",
    components: {
        Multiselect
    },
    data: function() {
        return {
            p_user: {},
            permissions: {},
            role: {}
        }
    },
    methods:{
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
        getPermission: function() {
            axios.get(state.urlAdminApi + '/get-all-permissions', {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.permissions = res.data;
                })
                .catch(this.catch);
        },
        getPermissionUser: function() {
            axios.get(state.urlAdminApi + '/get-permissions-role/' + this.$route.params.id, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    for (const item in res.data) {
                        if (res.data.hasOwnProperty(item)) {
                            this.p_user[res.data[item].name] = true;
                        }
                    }
                    this.getPermission();
                })
                .catch(this.catch);
        },
        getRole: function () {
            axios.get(state.urlAdminApi + '/get-role/' + this.$route.params.id, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.role = res.data;
                })
                .catch(this.catch);
        },
        changePermission: function () {
            //patchPermissionsRole
            axios.patch(state.urlAdminApi + '/patch-permissions-role/' + this.$route.params.id, {permissions: this.p_user}, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data);
                })
                .catch(this.catch);
        }
    },
    created() {
        this.getPermissionUser();
        this.getRole();
    }
}
</script>

<style scoped>

</style>
