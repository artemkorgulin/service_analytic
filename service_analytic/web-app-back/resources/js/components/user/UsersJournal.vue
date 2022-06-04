<template>
    <div>
        <h2>Список пользователей</h2>
        <a class="btn btn-primary"  v-bind:href="'/admin/user/create'">новый пользователь</a>
        <table class="table table-striped table-hover">
            <thead >
            <tr >
                <th scope="col">id</th>
                <th scope="col">ФИО</th>
                <th scope="col">Email</th>
                <th scope="col">Телефон</th>
                <th scope="col" title="Указать количество дней до окончания">Тариф</th>
                <th scope="col">Маркетплейсы</th>
                <th scope="col">Баланс</th>
                <th scope="col">Дата регистрации</th>
                <th scope="col">Дата авторизации</th>
                <th scope="col">Роль пользователя</th>
                <th scope="col" title="Активен, Заблокирован, Удален">Статус пользователя</th>
                <th scope="col" title="Количество активных товаров у пользователя">активные товары</th>
                <th></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="user of users.data">
                <td>{{user.id}}</td>
                <td>{{user.name}}</td>
                <td>{{user.email}}</td>
                <td>{{user.phone}}</td>
                <td>{{user.description}}</td>
                <td>{{user.title}}</td>
                <td>{{user.balance}}</td>
                <td>{{getDate(user.created_at)}}</td>
                <td>{{getDate(user.updated_at)}}</td>
                <td>
                    <span v-for="(role, k) of user.roles">
                        <template  v-if="k === user.roles.length-1" >
                            {{role.name}}
                        </template>
                        <template v-else>
                            {{role.name}}/
                        </template>
                    </span>
                </td>
                <td>{{user.status}}</td>
                <td title="ozon/wildberries">{{user.op_cnt}}/{{user.wb_cnt}}</td>
                <td><a @click="route('UserEdit', user.id)"><i class="fas cursor-pointer fa-edit text-primary"></i></a></td>
                <td><i v-on:click="deleteUser(user.id)" class="fa cursor-pointer fa-trash text-danger" aria-hidden="true"></i></td>

            </tr>
            </tbody>
        </table>
        <pagination :limit="5" :data="users" @pagination-change-page="getAllUsers"></pagination>
    </div>
</template>

<script>
import state from "../../store/state";


export default {
    name: "UsersJournal",
    props: {

    },
    data : function() {
        return {
            users: {},
            page: 1,
            perPage: 9,
            pages: [],
        }
    },
    methods: {
        getDate (d) {
            let today  = new Date(d);
            return today.toLocaleDateString("ru-Ru")

        },
        catch: function(err){
            console.log(err);

            if (err.response) {
                if(typeof err.response.data.error === 'string'){
                    this.showError(err.response.data.error);
                } else {
                    this.showError(err.response.data.error.message);
                    if(err.response.data.error.advanced){
                        for(const error of err.response.data.error.advanced){
                            for(let item in error){
                                this.showError(error[item]);
                            }
                        }
                    }
                }
            }
        },
        showError: function (err) {
            this.$toasted.error(err, {
                theme: "toasted-primary",
                position: "top-right",
                duration : 5000
            });
        },
        route (name, id) {
            if(id)
                this.$router.push({ name: name, params: { id: id}});
            else
                this.$router.push({ name: name});
        },
        getAllUsers(page = 1) {
            this.page = page;
            this.$http.get(state.urlAdminApi + '/get-all-users' + '?page=' + this.page, {
                headers: {Authorization: state.tokenApi}
            })
                .then(response => {
                    return response.json();
                })
                .then(data => {
                    this.users = data;
                })
                .catch(this.catch);
        },
        deleteUser: function(id){
            axios.delete(state.urlAdminApi + '/delete-user/' + id, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    this.showSuccess(res.data);
                    this.getAllUsers(this.page);
                })
                .catch(this.catch);

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
    },

    created:  function () {
        this.getAllUsers();
    },
    filters: {
        trimWords(value){
            return value.split(" ").splice(0,20).join(" ") + '...';
        }
    }
}
</script>

<style scoped>
button.page-link {
    display: inline-block;
}
button.page-link {
    font-size: 15px;
    color: rgb(52, 144, 220);
    font-weight: 450;
}
.offset{
    width: 500px !important;
    margin: 20px auto;
}
.cursor-pointer{
    cursor: pointer;
}
</style>
