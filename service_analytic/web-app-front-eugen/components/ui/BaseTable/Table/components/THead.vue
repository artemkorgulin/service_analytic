<template>
    <thead>
        <tr>
            <slot />
        </tr>
    </thead>
</template>

<script>
    export default {
        name: 'THead',
    };
</script>

<style scoped lang="scss">
    thead {
        border-top: 1px solid $border-color;
        border-bottom: 1px solid $border-color;
    }
</style>
