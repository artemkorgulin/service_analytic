<template>
    <div>
        <div class="row">
            <div class="card col" style="padding: 10px; margin:0 10px 30px 10px">
                <div class="row justify-content-center">
                    <h3 v-if="id">редактировать позицию</h3>
                    <h3 v-else>новая позиция</h3>
                </div>
                <div class="col-md-6">
                    <label class="typo__label">название</label>
                    <input type="text" v-model="name">
                </div>
                <div class="col-md-6">
                    <label class="typo__label">категория</label>
                    <multiselect v-model="categories" :options="category_id" :multiple="false" :close-on-select="false" :clear-on-select="false" :preserve-search="true" placeholder="Pick some" label="name" track-by="name" :preselect-first="true"></multiselect>
                </div>
                <div class="col-md-6">
                    <label class="typo__label">цена</label>
                    <input type="text" v-model="data">
                </div>

            </div>
        </div>
    </div>

</template>

<script>
import Multiselect from "vue-multiselect";

export default {
    name: "GoodsWb",
    components: {
        Multiselect
    },
    data(){
        return {
            id: this.$route.params.id
        }
    },
    created() {

    }
}
</script>

<style scoped>

</style>
