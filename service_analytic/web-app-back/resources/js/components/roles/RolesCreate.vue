<template>
    <div>
        <h2>Создать новую роль</h2>
        <form ref="form">
            <div class="col-md-6">
                <label class="form-label">Выбрать модель к которой привязать роль</label>
                <multiselect aria-describedby="modelHelp" v-model="model" :options="pull_model" :multiple="false"
                             :close-on-select="true" :clear-on-select="false" placeholder="Выберите модель" label="name"
                             track-by="name"></multiselect>
                <div id="modelHelp" class="form-text">Выберите модель</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="name">Название роли</label>
                <input class="form-control" type="text" id="name" name="name" v-model="role">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="description">Описание роли</label>
                <input class="form-control" type="text" id="description" name="description" v-model="description">
            </div>
            <br>
            <button class="btn btn-primary" @click="addRole" type="button">Добавить</button>
        </form>
    </div>
</template>

<script>
import state from "../../store/state";
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
export default {
    name: "RolesCreate",
    components: {
        Multiselect
    },
    data : function() {
        return {
            url: '',
            role: '',
            description: '',
            model: {id: 1, name: 'Модель пользователей', model: 'App\\Models\\User'},
            pull_model: []
        }
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
            if (err.response) {
                if (typeof err.response.data.error === 'string') {
                    this.showError(err.response.data.error);
                } else {
                    if(err.response.data.error){
                        this.showError(err.response.data.error.message);
                        if (err.response.data.error.advanced) {
                            for (const error of err.response.data.error.advanced) {
                                for (let item in error) {
                                    this.showError(error[item]);
                                }
                            }
                        }
                    } else {
                        this.showError(err.response.data);
                    }
                }
            } else if (err.request) {
                this.showError(String(err.request));
            } else {
                this.showError(String(err));
            }
        },
        getModels: function(){
            axios.get(state.urlAdminApi + '/get-models', {headers: {Authorization: state.tokenApi}})
            .then((res) => { this.pull_model = res.data })
            .catch(this.catch);
        },
        addRole: function () {
          let self = this;
            axios.post(state.urlAdminApi + '/add-roles', {
                role: this.role,
                description: this.description,
                model: this.model.model,
                id: this.model.id
            }, {headers: {Authorization: state.tokenApi}})
          .then((res) => {
              this.showSuccess('новая роль успешно добавлена');
              self.$router.push({ name: 'Roles' })
          }).catch(this.catch);
        }
    },
    created() {
        this.getModels();
    }
}
</script>

<style scoped>

</style>
