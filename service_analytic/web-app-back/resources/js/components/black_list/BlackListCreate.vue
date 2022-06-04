<template>
    <div  class="container">
        <div class="row">
            <div class="col">
                <h3>Добавить в черный список бренд</h3>
            </div>
        </div>

        <form action="">
            <div class="row">
                <div class="col">
                    <label class="form-label">Название бренда</label>

                    <multiselect @search-change="getBrands"  aria-describedby="brandHelp"    v-model="brand" :options="brands" :multiple="false" :close-on-select="true" :clear-on-select="false"  placeholder="Pick some" label="name" track-by="name" ></multiselect>
                    <div id="brandHelp" class="form-text">введите название бренда</div>
                </div>
                <div class=col>
                    <label for="manager" class="form-label">менеджер</label>
                    <input type="text" v-model="manager" class="form-control" id="manager" aria-describedby="managerHelp">
                    <div id="managerHelp" class="form-text">введите имя менеджера</div>
                </div>

            </div>
            <div class="row">

                <div class="col">
                    <label class="form-label">Статус номенклатуры</label>
                    <multiselect  aria-describedby="statusdHelp"    v-model="status" :options="status_pull" :multiple="false" :close-on-select="true" :clear-on-select="false"  placeholder="Pick some" label="name" track-by="name" ></multiselect>
                    <div id="statusdHelp" class="form-text">Введите статус номенклатуры</div>
                </div>

                <div class="col">
                    <label cor="pattern" class="form-label">паттерн</label>
                    <input type="text" v-model="pattern" class="form-control" id="pattern" aria-describedby="patternHelp">

                    <div id="patternHelp" class="form-text">создать паттерн</div>
                </div>
            </div>

            <div>
                <div class="row">
                    <div class=" form-check" style="margin-top: 30px">
                        <input v-model="ozon" type="checkbox" class="form-check-input" id="ozon">
                        <label class="form-check-label" for="ozon" aria-describedby="statusHelp2">ozon</label>
                        <div id="ozonHelp" class="form-text">добавить исключение в ozon</div>
                    </div>
                    <div class=" form-check" style="margin-top: 30px">
                        <input v-model="wb" type="checkbox" class="form-check-input" id="wildberries">
                        <label class="form-check-label" for="wildberries" aria-describedby="statusHelp2">wildberries</label>
                        <div id="wildberriesHelp" class="form-text">добавить исключение в wildberries</div>
                    </div>
                </div>
            </div>
            <div>

            </div>
            <div class="row">
                <div class="text-center"  style="margin-top: 15px;"><button type="button" v-on:click="save"  data-v-45f4e59e="" class="btn-primary btn">сохранить изменения</button></div>
            </div>
        </form>
    </div>
</template>

<script>
import state from "../../store/state";
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
export default {
    name: "BlackListCreate",
    components: {
        Multiselect
    },
    data() {
        return {
            brands:[],
            brand: {},
            partner: null,
            manager: null,
            pattern: null,
            status: null,
            ozon: null,
            wb: null,
            obj: {},
            status_pull : [
                { name: 'вывод' },
                { name: 'актив' }
            ],
        };
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
        getBrands(search = ''){
            let url;
            if(search)
                url = state.urlAdminApi + '/get-brands/' + search
            else
                url = state.urlAdminApi + '/get-brands'

            //get-brands
            axios.get(url,{ headers: { Authorization: state.tokenApi }})
                .then((res)=> { this.brands = res.data })
                .catch(this.catch);
        },
        save(){
            axios.post(state.urlApiVA + '/black-list-brands/',
                {
                    manager: this.manager,
                    brand: this.brand.name,
                    status: this.status.name,
                    pattern: this.pattern,
                    ozon: this.ozon ? 1 : 0,
                    wildberries: this.wb ? 1 : 0
                },
                {headers: {Authorization: state.tokenApi}})
                .then((res)=> {
                    this.showSuccess('Добавлен новый бренд в черный список');
                    this.$router.push({ name: 'BlackLIst'})
                })
                .catch(this.catch);
        }
    },
    created(){
        this.getBrands();
    }
}
</script>

<style scoped>

</style>
