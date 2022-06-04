<template>
    <div ref="page" :class="$style.statisticPage">
        <AdmChartBlock
            :items="items"
            :counters="counters"
            :date-facet="dateFacet"
            :dates="dates"
            @dateChange="handleDateChange"
        />
    </div>
</template>
<router lang="js">
  {
    name: 'campaign-statistic',
    path: '/:marketplace/campaign/:id/statistic',
    meta:{
        pageGroup: "perfomance",
        redirectOnChangeMarketplace: true,
        fallbackRoute: {
            name: "adm-campaigns"
        },
        isEnableGoBackOnMobile:true,
        name: "statistic",
    }
  }
</router>
<script>
    /* eslint-disable no-unused-expressions,camelcase,no-empty-function */

    export default {
        name: 'AdmStatisticPage',
        data() {
            return {
                isMounted: false,

                counters: null,
                pageData: {},
                items: [],
                dateFacet: {
                    min: null,
                    max: null,
                },

                loading: false,
                dates: [],
                perPage: 25,
                pageErrors: [],
                // pageTitle: '',
                // periodSelected: null,
                // resizeObserver: null,
                // viewSelected: 0,
                // areaHeight: '100%',
            };
        },
        async fetch() {
            try {
                const { items, counters, campaign, pageData } = await this.$store.dispatch(
                    'campaign/fetchStatistic',
                    {
                        params: this.filtersToRequest,
                    }
                );
                this.counters = counters;
                this.pageData = pageData;
                this.items = items;
                this.dateFacet = {
                    min: campaign.start_date,
                    max: new Date().toISOString(),
                };
                // console.log('🚀 ~ file: statistic.vue ~ line 78 ~ fetch ~ datdataStata', dataStat);
                // const response = await this.$axios.$get('/api/adm/v1/get-analytics-list', {
                //     params: this.filtersToRequest,
                // });
                // const {
                //     data: {
                //         campaigns: { data, counters, campaign, ...pageData },
                //     },
                // } = response;
                // if (!campaign) {
                //     throw new Error('Ошибка при получении данных с сервера');
                // }
                // if (!campaign.start_date && this.$nuxt.$config.ENABLE_WARNINGS) {
                //     this.pageErrors.push({
                //         timeout: 3000,
                //         message: '[Server][pages/campaign/_id.vue] no campaign start_date',
                //     });
                // }
                // const startDate = campaign.start_date;
                // if (!startDate && this.$nuxt.$config.ENABLE_WARNINGS) {
                //     this.pageErrors.push({
                //         timeout: 3000,
                //         message: '[Server][pages/campaign/_id.vue] no campaign created_at',
                //     });
                // }
                // if (String(startDate) === '0000-00-00') {
                //     if (this.$nuxt.$config.ENABLE_WARNINGS) {
                //         this.pageErrors.push({
                //             timeout: 3000,
                //             message:
                //                 '[Server][pages/campaign/_id.vue] start_date = "0000-00-00" - invalid date',
                //         });
                //     }
                //     if (campaign.created_at) {
                //         startDate = campaign.created_at;
                //     }
                // }
                // startDate,

                // this.campaignData = campaign;
                // this.pageTitle = campaign.name;
            } catch (error) {
                await this?.$sentry?.captureException(error);
                console.log('🚀 ~ file: statistic.vue ~ line 182 ~ fetch ~ error', error);

                throw new Error(error);
            }
        },
        // head() {
        //     return {
        //         title: this.pageTitle,
        //     };
        // },
        computed: {
            filtersToRequest() {
                return {
                    campaigns: [this.$route.params.id],
                    ...(this.dates?.length > 1 ? { from: this.dates[0], to: this.dates[1] } : null),
                    per_page: this.perPage,
                };
            },
            isEnd() {
                return this.pageData.current_page === this.pageData.last_page;
            },
        },
        // watch: {
        //     periodSelected: {
        //         immediate: true,
        //         handler(val) {
        //             if (isUnset(val)) {
        //                 this.dates = [];
        //                 return;
        //             }
        //             const today = moment().format();
        //             switch (val) {
        //                 case 0:
        //                     // YESTERDAY
        //                     this.dates = [moment().subtract(1, 'days').format(), today];
        //                     break;
        //                 case 1:
        //                     // WEAK
        //                     this.dates = [moment().subtract(7, 'days').format(), today];
        //                     break;
        //                 case 2:
        //                     // MONTH
        //                     this.dates = [moment().subtract(30, 'days').format(), today];
        //                     break;
        //                 case 3:
        //                     // QUATER
        //                     this.dates = [moment().subtract(90, 'days').format(), today];
        //                     break;
        //                 case 4:
        //                     // YEAR
        //                     this.dates = [moment().subtract(365, 'days').format(), today];
        //                     break;
        //                 default:
        //                     break;
        //             }
        //         },
        //     },
        //     dates() {
        //         this.$fetch();
        //     },
        //     viewSelected(val) {
        //         if (val === 1) {
        //             return this.$nextTick(() => this.handleSetScrollAreaHeight());
        //         }
        //     },
        // },
        // mounted() {
        //     this.isMounted = true;
        //     this.resizeObserver = new ResizeObserver(this.handleSetScrollAreaHeight);
        //     this.resizeObserver.observe(this.$refs.page);
        // },
        // beforeDestroy() {
        //     this.resizeObserver && this.resizeObserver.unobserve(this.$refs.page);
        // },
        methods: {
            // handleSetScrollAreaHeight() {
            //     if (!this.$refs?.scrollableTable) {
            //         return;
            //     }
            //     return this.$nextTick(() => this.$refs.scrollableTable.setScrollAreaHeight());
            // },
            handleDateChange(val) {
                // this.periodSelected = null;
                this.dates = val;
            },
            // async handleReachEnd($state) {
            //     try {
            //         if (this.loading || this.isEnd) {
            //             return false;
            //         }
            //         this.loading = true;
            //         const {
            //             data: {
            //                 campaigns: { data, current_page: currentPage },
            //             },
            //         } = await this.$axios.$get('/api/adm/v1/get-analytics-list', {
            //             params: {
            //                 ...this.filtersToRequest,
            //                 page: this.pageData.current_page + 1,
            //             },
            //         });
            //         this.loading = false;
            //         this.pageData.current_page = currentPage;
            //         this.items = [...this.items, ...transformItems(data)];
            //         if (this.isEnd) {
            //             $state.complete();
            //         } else {
            //             $state.loaded();
            //         }
            //     } catch (error) {
            //         await this?.$sentry?.captureException(error);
            //         console.log('🚀 ~ file: _id.vue ~ line 305 ~ handleReachEnd ~ error', error);
            //         this.loading = false;
            //         await $state.complete();
            //     }
            // },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .statisticPage {
        overflow: hidden;
        display: flex;
        flex: 1;
        flex-direction: column;
        padding-top: 1px;
    }

    .mainArea {
        position: relative;
        overflow: hidden;
        flex: 1;
    }

    .datePicker {
        width: 30rem;
    }

    .filtersWrapper {
        display: flex;
        margin-bottom: 1.6rem;
        gap: 0.8rem;

        // @include respond-to(sm) {
        //     flex-direction: column;
        // }
    }

    .scrollableTable {
        position: relative;
        max-height: calc(100vh - 300px);
    }
</style>
