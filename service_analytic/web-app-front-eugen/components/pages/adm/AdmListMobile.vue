<template>
    <div :class="$style.AdmListMobile">
        <AdmListItemMobile
            v-for="item in items"
            :key="item.id"
            :class="$style.item"
            :item="item"
            :is-active="selectedItems.includes(item.id)"
            @select="handleSelect"
        />
        <div v-if="isMounted && isEnableInfinity && !isEnd" :class="$style.infinityWrapper">
            <InfiniteLoading
                ref="infinityLoading"
                :class="$style.infinityInner"
                force-use-infinite-wrapper="#scroll_area"
                @infinite="handleReachEnd"
            >
                <div slot="no-results"></div>
                <div slot="no-more"></div>
            </InfiniteLoading>
        </div>
    </div>
</template>

<script>
    import InfiniteLoading from 'vue-infinite-loading';
    import { isUnset } from '~utils/helpers';

    export default {
        name: 'AdmListMobile',
        components: {
            InfiniteLoading,
        },
        props: {
            value: {
                type: [Array],
                default: () => [],
            },
            blockingLoading: {
                type: Boolean,
                default: false,
            },
            items: {
                type: Array,
                default: () => [],
            },
            isEnableInfinity: {
                type: Boolean,
                default: false,
            },
            isEnd: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                isMounted: false,
            };
        },
        computed: {
            selectedItems: {
                get() {
                    return this.value;
                },
                set(val) {
                    return this.$emit('input', val);
                },
            },
            isAllSelected() {
                return !this.selectedItems.length;
            },
        },
        mounted() {
            this.isMounted = true;
        },
        methods: {
            handleReachEnd($state) {
                this.$emit('end', $state);
            },
            // handleExpand() {
            //     console.log('🚀 ~ file: AdmListMobile.vue ~ line 82 ~ handleExpand ~ handleExpand');
            // },
            handleSelect(payload) {
                if (isUnset(payload)) {
                    this.selectedItems = [];
                    return;
                }
                const index = this.selectedItems.indexOf(payload.id);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                    return;
                }
                this.selectedItems.push(payload.id);
            },
        },
    };
</script>

<style lang="scss" module>
    $size: 48px;

    .infinityWrapper {
        width: 100%;
    }
</style>
