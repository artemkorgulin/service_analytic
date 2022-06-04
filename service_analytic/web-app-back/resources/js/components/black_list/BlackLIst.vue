<template>
    <div>
        <h3>Черный список брендов</h3>
        <a class="btn btn-primary"  v-bind:href="'/admin/black_list/create'">добавить бренд</a>
        <table class="table table-striped table-hover">
            <caption>черный список брендов</caption>
            <thead>
                <tr>
                    <th>бренд</th>
                    <th>менеджер</th>
                    <th>Статус номенклатуры</th>
                    <th>wildberries</th>
                    <th>ozon</th>
                    <th>паттерн</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="v of list_black.data">
                    <td>{{v.brand}}</td>
                    <td>{{v.manager}}</td>
                    <td>{{v.status}}</td>
                    <td><input v-on:change="patchBlackList(v.id, v)" type="checkbox" v-model="v.wildberries"></td>
                    <td><input v-on:change="patchBlackList(v.id, v)" type="checkbox" v-model="v.ozon"></td>
                    <td>{{v.pattern}}</td>
                    <td><a @click="route('BlackListEdit', v.id)"><i class="fas cursor-pointer fa-edit text-primary"></i></a></td>
                    <td><i v-on:click="deleteBrand(v.id)" class="fa cursor-pointer fa-trash text-danger" aria-hidden="true"></i></td>
                </tr>
            </tbody>
        </table>
        <pagination :limit="5" :data="list_black" @pagination-change-page="getBlackList"></pagination>
    </div>
</template>

<script>
import state from "../../store/state";

export default {
    name: "BlackLIst",
    data() {
      return {
        list_black: {},

      }
    },
    methods:{
        getBlackList: function(page = 1){

            axios.get(state.urlApiVA + '/black-list-brands' + '?page=' + page, {headers: {Authorization: state.tokenApi}})
                    .then((res)=> {
                        this.list_black = res.data;
                    })
                    .catch(this.catch);

        },
        patchBlackList: function(id, obj) {
            axios.put(state.urlApiVA + '/black-list-brands/' + id, {
                manager: obj.manager,
                brand: obj.brand,
                status: obj.status,
                ozon: obj.ozon ? 1 : 0,
                pattern: obj.pattern,
                wildberries: obj.wb ? 1 : 0
            }, {headers: {Authorization: state.tokenApi}})
                .then((res) => {
                    //this.list_black = res.data;
                })
                .catch(this.catch);
        },

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
        deleteBrand(id){
            axios.delete(state.urlApiVA + '/black-list-brands/' + id, {headers: {Authorization: state.tokenApi}})
                .then((res)=> {
                    this.list_black = res.data;
                    this.showSuccess('бренд из черного списка удален');
                })
                .catch(this.catch)
        },
        route (name, id) {
            if(id)
                this.$router.push({ name: name, params: { id: id}});
            else
                this.$router.push({ name: name});
        },
    },
    created() {
        this.getBlackList();
    }
}
</script>

<style scoped>

</style>
