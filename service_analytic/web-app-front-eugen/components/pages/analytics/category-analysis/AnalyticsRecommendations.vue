<template>
    <div class="analytics-recommendations">
        <div class="analytics-recommendations__header">
            <h2 class="analytics-recommendations__title">Рекомендации</h2>
                <VSelect
                    v-model="selectValue"
                    class="analytics-recommendations__select light-outline"
                    :items="brandsTable"
                    :item-text="itemText"
                    :item-value="itemValue"
                    outlined
                    dense
                    append-icon="$expand"
                    :menu-props="{ nudgeBottom: 42 }"
                />
        </div>
        <AnalyticsRecommendationItem
            v-for="(item, index) in recommendations"
            :key="index"
            :index="index"
            :text="item"
        />
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    export default {
        name: 'AnalyticsRecommendations',
        props: {
            itemText: {
                type: String,
                default: 'brand',
            },
            itemValue: {
                type: String,
                default: 'brand_id',
            },
        },
        data() {
            return {
                selectValue: null,
            };
        },
        computed: {
            ...mapState('category-analysis', {
                brandsData: 'brandsData',
                brandsTable: 'brandsTable',
            }),
            recommendations() {
                if (
                    this.selectValue &&
                    this.brandsData &&
                    this.brandsData[this.selectValue]?.quality
                ) {
                    return Object.values(this.brandsData[this.selectValue].quality);
                } else {
                    return null;
                }
            },
        },
        watch: {
            brandsTable(val, oldVal) {
                if (val.length && !oldVal.length) {
                    this.selectValue = val[0].brand_id;
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .analytics-recommendations {
        @include flex-grid-y;

        &__header {
            @include flex-grid-x;

            align-items: center;
        }

        &__title {
            font-size: 20px;
            font-weight: 500;
            line-height: 1.35;
        }

        &__select {
            flex: none;
            width: 150px;
        }
    }
</style>
