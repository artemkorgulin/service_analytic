<template>
    <div class="tabs-prices">
        <v-form ref="form">
            <div class="tabs-prices__control">
                <v-text-field-money
                    v-model="dataPriceFieldMin"
                    class="tabs-prices__control-range light-outline"
                    label="Min"
                    :properties="{
                        prefix: '',
                        readonly: false,
                        disabled: false,
                        dense: true,
                        outlined: true,
                        clearable: false,
                        rules: validateRules.min,
                        placeholder: ' ',
                    }"
                    :options="{
                        locale: 'ru-RU',
                        length: 7,
                        precision: 0,
                        empty: null,
                    }"
                    @change="debounceHandleRange"
                />
                <v-range-slider
                    class="tabs-prices__control-slider"
                    :value="[dataPrices.min, dataPrices.max]"
                    :min="startPriceRange.min"
                    :max="startPriceRange.max"
                    :thumb-size="0"
                    :ripple="false"
                    @change="rangeHadler"
                >
                    <template #thumb-label="{ value }">
                        <span class="tabs-prices__control-label">
                            {{ currencyFormatter(value, sign) }}
                        </span>
                    </template>
                </v-range-slider>
                <v-text-field-money
                    v-model="dataPriceFieldMax"
                    class="tabs-prices__control-range light-outline"
                    label="Max"
                    :properties="{
                        prefix: '',
                        readonly: false,
                        disabled: false,
                        dense: true,
                        outlined: true,
                        clearable: false,
                        rules: validateRules.max,
                        placeholder: ' ',
                    }"
                    :options="{
                        locale: 'ru-RU',
                        length: 7,
                        precision: 0,
                        empty: null,
                    }"
                />
                <v-text-field
                    v-model="segment"
                    class="tabs-prices__control-input light-outline"
                    type="number"
                    dense
                    outlined
                    :rules="validateRules.segments"
                    label="Количество сегментов"
                ></v-text-field>
            </div>
        </v-form>
        <div class="tabs-prices__loader" style="min-height: 300px">
            <div v-if="isLoadPrices" class="tabs-prices__loader-circular">
                <VProgressCircular indeterminate size="50" color="accent" />
            </div>
            <template v-if="isFirstLoading">
                <PricesChartBlock :prices="prices" />
                <SeTableAG :page-size="50" :columns="columnDefs" :rows="rows" :pagination="false" />
            </template>
        </div>
    </div>
</template>
<script>
    /* eslint-disable */
    import { sortNumValuesAgGrid } from '~/assets/js/utils/helpers';
    import SeTableAG from '~/components/ui/SeTableAG';
    import { debounce } from 'lodash';
    import PricesChartBlock from '~/components/pages/analytics/categories/PricesChartBlock';
    import { mapState } from 'vuex';
    export default {
        components: {
            SeTableAG,
            PricesChartBlock,
        },
        props: {
            loadData: {
                type: Function,
                default: () => ({}),
            },
            setDataPrices: {
                type: Function,
                default: () => ({}),
            },
            dataPrices: {
                type: Object,
                default: () => ({}),
            },
            startPriceRange: {
                type: Object,
                default: () => ({}),
            },
            prices: {
                type: Array,
                default: () => [],
            },
            isLoadPrices: Boolean,
            isFirstLoading: Boolean,
        },
        data: () => ({
            filter: {
                range: [10000, 80000],
                segments: 24,
            },
            sign: '₽',
            maxPrise: 20000,
            minPrise: 0,

            colSort: ['min_range', 'max_range'],
            colHide: ['subject_id', 'web_id'],
        }),
        computed: {
            ...mapState('categories-analitik', ['pricesColums']),

            columnDefs() {
                return this.pricesColums.map((col, index) => {
                    const isNumberFilterObject = !this.colSort.includes(col.field)
                        ? {
                              sortable: true,
                              filter: 'agNumberColumnFilter',
                          }
                        : {};

                    return {
                        ...col,
                        ...isNumberFilterObject,
                        hide: this.colHide.includes(col.field),
                    };
                });
            },
            rows() {
                return JSON.parse(JSON.stringify(this.prices)).map(item => {
                    Object.keys(item).forEach(key => {
                        if (!this.colSort.includes(item[key])) {
                            item[key] = Number(item[key]);
                        }
                    });
                    return item;
                });
            },
            validateRules() {
                /* eslint-disable */
                const { min, max } = this.startPriceRange;
                const cp = v => (typeof v === 'string' ? Number(v.replace(/\s/g, '')) : v);
                return {
                    min: [
                        v => Boolean(v) || 'Обязательно',
                        v => cp(v) >= min || `мин ${min}`,
                        v => cp(v) < max || `макс ${max - 1}`,
                    ],
                    max: [
                        v => Boolean(v) || 'Обязательно',
                        v => cp(v) <= max || `макс ${max}`,
                        v => cp(v) > min || `мин ${min + 1}`,
                    ],
                    segments: [
                        v => Boolean(v) || 'Обязательно',
                        v => cp(v) >= 1 || 'мин 1',
                        v => cp(v) <= 25 || 'макс 25',
                    ],
                };
            },
            segment: {
                get() {
                    return this.dataPrices.segment;
                },
                set(value) {
                    this.setDataPrices({
                        segment: Number(value),
                    });

                    this.debounceHandleRange();
                },
            },
            dataPriceFieldMin: {
                get() {
                    return this.dataPrices.min;
                },
                set(value) {
                    this.setDataPrices({
                        min: Number(value),
                        max: this.dataPrices.max,
                        segment: this.dataPrices.segment,
                    });

                    this.debounceHandleRange();
                },
            },
            dataPriceFieldMax: {
                get() {
                    return this.dataPrices.max;
                },
                set(value) {
                    this.setDataPrices({
                        min: this.dataPrices.min,
                        max: Number(value),
                        segment: this.dataPrices.segment,
                    });

                    this.debounceHandleRange();
                },
            },
        },
        created() {
            this.debounceHandleRange = debounce(this.validateForms, 500);
        },
        async mounted() {
            await this.loadData();
        },
        methods: {
            async rangeHadler(value) {
                this.setDataPrices({
                    min: value[0],
                    max: value[1],
                    segment: this.dataPrices.segment,
                });
                if (!this.$refs.form.validate()) return;
                this.loadData();
            },

            async validateForms() {
                if (!this.$refs.form.validate()) return;
                await this.loadData();
            },
            currencyFormatter(currency, sign) {
                const sansDec = Number(currency).toFixed(0);
                const formatted = sansDec.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
                return `${formatted} ${sign}`;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .tabs-prices {
        padding: 16px;

        &__loader {
            position: relative;

            &-circular {
                position: absolute;
                top: 0;
                left: 0;
                z-index: 99;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.4);
            }
        }

        &__error {
            margin-top: 20px;
            padding: 10px;
            border-radius: 10px;
            background: #ffe8e8;
            text-align: center;
            font-size: 18px;
            color: #606a7c;
        }

        &__control {
            display: flex;
            padding-top: 30px;

            &-range {
                max-width: 96px;
            }

            &-slider {
                padding: 0 20px;
            }

            &-input {
                max-width: 200px;
                margin-left: 16px !important;
            }

            &-label {
                display: flex;
                margin-bottom: 40px;
                padding: 6.5px 12px;
                border-radius: 200px;
                background: rgba(113, 11, 255, 0.08);
                text-align: center;
                white-space: nowrap;
                font-weight: bold;
                font-size: 14px;
                line-height: 19px;
                color: $primary-color;
            }
        }
    }
</style>
<style lang="scss">
    .tabs-prices {
        .apexcharts-legend-series {
            padding: 0 10px;
        }

        .v-slider__track-background.primary {
            border-color: #e9edf2 !important;
            background-color: #e9edf2 !important;
        }
    }
</style>
