export default {
    data() {
        return {
            pageErrors: [],
            pageErrorsCallbacks: [],
        };
    },
    mounted() {
        return this.$_generateErrors();
    },
    beforeDestroy() {
        return this.$_hideErrors();
    },
    methods: {
        $_generateErrors() {
            if (!this.pageErrors?.length) {
                return;
            }
            return this.$nextTick(async () => {
                for (const err of this.pageErrors) {
                    await this.$_createNotification({
                        message: err,
                        type: 'negative',
                        timeout: 0,
                    });
                }
            });
        },
        $_addError(payload, isParse = false) {
            const error = isParse ? this.$_getResponseErrorMessage(payload) : payload;
            this.pageErrors.push(error);
        },
        $_hideErrors() {
            if (!this.pageErrorsCallbacks?.length) {
                return;
            }
            for (const pageCallback of this.pageErrorsCallbacks) {
                pageCallback();
            }
        },
        async $_createNotification(payload) {
            console.log(
                '🚀 ~ file: pageErrors.js ~ line 35 ~ $_createNotification ~ payload',
                payload
            );
            return this.pageErrorsCallbacks.push(await this.$notify.create(payload));
        },
        $_getResponseErrorMessage(error) {
            return (
                error?.response?.statusText ||
                error?.message ||
                'Произошла ошибка при получении данных'
            );
        },
    },
};
