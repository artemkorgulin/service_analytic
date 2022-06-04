<template>
    <AdmChartBlock
        v-model="dates"
        :is-fetched="dataFetched"
        :class="$style.AdmChartMultiple"
        :counters="counters"
        :items="items"
        :date-facet="dateFacet"
        :mobile="mobile"
    />
</template>

<script>
    /* eslint-disable no-extra-parens */
    export default {
        name: 'AdmChartMultiple',
        props: {
            campaigns: {
                type: Array,
                default: () => [],
            },
            mobile: {
                type: Boolean,
                default: false,
            },
        },
        data() {
            return {
                dates: [],
                counters: null,
                items: [],
                dateFacet: {
                    min: null,
                    max: null,
                },
                dataFetched: false,
            };
        },
        async fetch() {
            try {
                const { items, counters } = await this.$store.dispatch('campaign/fetchStatistic', {
                    params: this.filtersToRequest,
                });
                this.counters = counters;
                this.items = items;
                this.dateFacet = {
                    min: null,
                    max: new Date().toISOString(),
                };
                this.dataFetched = true;
            } catch (err) {
                console.log('🚀 ~ file: AdmChartBlock.vue ~ line 269 ~ fetch ~ err', err);
            }
        },
        computed: {
            filtersToRequest() {
                const toDate = date => this.$options.filters.formatDateTime(date, '$y-$m-$d');
                return {
                    campaign_ids: this.campaigns,
                    ...(this.dates?.length > 1
                        ? { start_date: toDate(this.dates[0]), end_date: toDate(this.dates[1]) }
                        : null),
                };
            },
        },
        watch: {
            campaigns() {
                return this.$fetch();
            },
            dates() {
                return this.$fetch();
            },
        },
    };
</script>
<style lang="scss" module>
    .AdmChartMultiple {
        //
    }
</style>
