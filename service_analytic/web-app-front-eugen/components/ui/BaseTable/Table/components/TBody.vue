<template>
    <tbody>
        <slot />
    </tbody>
</template>

<script>
    export default {
        name: 'TBody',
    };
</script>

<style scoped lang="scss">
    tbody {
        border-right: 1px solid $border-color;
        border-bottom: 1px solid $border-color;
    }
</style>
