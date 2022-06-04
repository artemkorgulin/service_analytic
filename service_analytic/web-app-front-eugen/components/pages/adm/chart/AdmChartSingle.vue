<template>
    <AdmChartBlock v-model="dates" :counters="counters" :items="items" :date-facet="dateFacet" />
</template>

<script>
    /* eslint-disable no-extra-parens */
    export default {
        name: 'AdmChartSingle',
        data() {
            return {
                dates: [],
                counters: null,
                items: [],
                dateFacet: {
                    min: null,
                    max: null,
                },
            };
        },
        async fetch() {
            try {
                const { items, counters, campaign } = await this.$store.dispatch(
                    'campaign/fetchStatistic',
                    {
                        params: this.filtersToRequest,
                    }
                );
                this.counters = counters;
                this.items = items;
                this.dateFacet = {
                    min: campaign.start_date,
                    max: new Date().toISOString(),
                };
            } catch (err) {
                console.log('🚀 ~ file: AdmChartBlock.vue ~ line 269 ~ fetch ~ err', err);
            }
        },
        computed: {
            filtersToRequest() {
                return {
                    campaigns: [this.$route.params.id],
                    ...(this.dates?.length > 1 ? { from: this.dates[0], to: this.dates[1] } : null),
                };
            },
        },
        watch: {
            dates() {
                return this.$fetch();
            },
        },
    };
</script>
