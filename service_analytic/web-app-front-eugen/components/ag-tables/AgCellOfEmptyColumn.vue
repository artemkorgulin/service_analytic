<template>
<div class="empty-column">
    <span>{{ message }}</span>
</div>
</template>

<script>
    export default {
        name: 'AgCellOfEmptyColumn',
        computed: {
            message() {
                return this.params.message || 'Нет данных';
            }
        }
    };
</script>

<style lang="scss" scoped>
.empty-column {
    display: flex;
    width: 100%;
    height: 100%;
    padding: 16px;
    background-color: $white;
    font-size: 14px;
    line-height: 1.5;
    color: $base-700;

    & span {
        max-width: 100%;
        white-space: normal;
    }
}
</style>
