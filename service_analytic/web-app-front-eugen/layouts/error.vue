<template>
    <ErrorBanner :class="$style.wrapper" :status-code="error.statusCode" :message="error.message" />
</template>

<script>
    export default {
        layout: 'empty',
        props: {
            error: {
                type: Object,
                default: null,
            },
        },
        data() {
            return {
                pageNotFound: '404 Not Found',
                otherError: 'An error occurred',
            };
        },
        head() {
            const title = this.error.statusCode === 404 ? this.pageNotFound : this.otherError;
            return {
                title,
            };
        },
        created() {
            console.log(this.error.message);
        },
    };
</script>

<style lang="scss" module>
    .wrapper {
        @include centerer();
    }
</style>
