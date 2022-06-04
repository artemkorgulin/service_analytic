export default {
    computed: {
        addMarketPlaceTick() {
            return this.$store.state.addMarketPlaceTick;
        },
    },
    watch: {
        form: {
            deep: true,
            handler() {
                this.$store.commit('setField', {
                    field: 'addMpBtnEnable',
                    value: !this.$changed || this.$invalid,
                });
            },
        },
        async addMarketPlaceTick(value) {
            if (!value) {
                return;
            }

            this.$store.commit('setField', { field: 'isLoadingAddMp', value: true });
            await this.handleSubmit();
            this.$store.commit('setField', { field: 'addMarketPlaceTick', value: false });
            this.$store.commit('setField', { field: 'isLoadingAddMp', value: false });
        },
    },
    created() {
        this.$store.commit('setField', {
            field: 'addMpBtnEnable',
            value: true,
        });
    },
};
