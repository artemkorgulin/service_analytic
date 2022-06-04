<template>
    <div>
        <h2>Роли</h2>
        <router-link class="btn btn-primary" to="/admin/roles/create">новая роль</router-link>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>название</th>
                    <th>описание</th>
                    <th>название защитника</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="role of roles">
                    <td>{{role.id}}</td>
                    <td><a v-bind:href="'/admin/permission/edit/' + role.id">{{role.name}}</a></td>
                    <td>{{ role.description }}</td>
                    <td>{{role.guard_name}}</td>
                </tr>
            </tbody>

        </table>
    </div>
</template>

<script>
import state from "../../store/state";

export default {
    name: "Roles",
    data : function() {
        return {
            roles: [],
        }
    },
    created:  function () {
        const self = this
        axios.get(state.urlAdminApi + '/get-roles', {
            headers: {
                Authorization: state.tokenApi,
            }
        })
            .then(function(res){
                self.roles = res.data;
            });
    },
}
</script>

<style scoped>

</style>
