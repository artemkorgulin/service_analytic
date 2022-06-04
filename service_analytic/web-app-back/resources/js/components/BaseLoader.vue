<template>
    <form @submit.prevent="submit" ref="formContainer">
        <div class="col-12" style="height: 100px" >Loading</div>
        <!-- your form inputs goes here-->
        <button class="btn btn-info" type="submit">Load</button>
    </form>
</template>

<script>
export default {
    methods: {
        submit() {
            let localLoader = this.$loading.show({container: this.$refs.formContainer, canCancel: false, color: '#956144'});
            //this.$store.dispatch('showGlobalLoader', {canCancel: true});

            axios.get('/admin/users?search=cn&page=' + 1).then(({ data }) => {
                this.users = data.data;
                //this.$store.dispatch('hideGlobalLoader');
                localLoader.hide();
            });
        }
    }
}
</script>
