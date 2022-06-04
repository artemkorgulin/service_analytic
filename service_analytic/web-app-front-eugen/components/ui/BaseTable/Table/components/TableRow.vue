<template>
    <tr>
        <slot />
    </tr>
</template>

<script>
    export default {
        name: 'TableRow',
    };
</script>

<style scoped lang="scss">
    tr {
        height: 40px;
    }
</style>
