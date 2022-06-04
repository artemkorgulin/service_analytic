<template>
    <div v-if="nameSortField && name === nameSortField" class="pt-1">
        <v-progress-circular
            v-if="sortLoading"
            size="14"
            indeterminate
            color="primary"
        ></v-progress-circular>
        <SvgIcon
            v-else-if="sortTypeCounter < 3"
            name="outlined/arrowUp"
            color="#710BFF"
            :class="{ desc: sortTypeCounter === 2 }"
            style="height: 16px"
        ></SvgIcon>
    </div>
    <div v-else></div>
</template>

<script>
    import { mapState } from 'vuex';
    export default {
        props: {
            name: {
                type: String,
                default: '',
            },
            ascSort: Boolean,
        },
        computed: {
            ...mapState('products', ['nameSortField', 'sortLoading', 'sortTypeCounter']),
        },
    };
</script>

<style lang="scss" scoped>
    .desc {
        transform: rotate(180deg);
    }
</style>
