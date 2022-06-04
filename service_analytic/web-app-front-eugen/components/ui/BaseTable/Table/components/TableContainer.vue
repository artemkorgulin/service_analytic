<template>
    <div class="table__container">
        <table class="table">
            <slot />
        </table>
    </div>
</template>

<script>
    export default {
        name: 'TableContainer',
    };
</script>

<style scoped lang="scss">
    .table__container {
        overflow: auto;

        .table {
            min-width: 100%;
            border-collapse: collapse;
        }
    }
</style>
