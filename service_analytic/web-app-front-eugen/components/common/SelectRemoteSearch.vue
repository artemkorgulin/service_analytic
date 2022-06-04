<template>
    <div :class="$style.SelectRemoteSearch">
        <template v-if="onlyDictionaryComputed">
            <v-autocomplete
                ref="select"
                v-model="input"
                :class="{ [$style.HasTooltip]: hasTooltip }"
                :items="getResults"
                :search-input.sync="query"
                :rules="rules"
                :label="label"
                :loading="pending"
                class="light-inline mt-0 pt-0"
                :dense="!noDense"
                v-bind="$attrs"
                multiple
                chips
                color="#710bff"
                item-text="value"
                item-value="id"
                @change="$emit('changeVal')"
                @focus="customFocus($event)"
                @blur="firstInt = true"
            >
                <template #append>
                    <SvgIcon
                        name="outlined/search"
                        style="height: 13px"
                        class="search-input__btn mr-1"
                    />
                </template>
                <template #selection="data">
                    <template v-if="!showAll">
                        <v-chip
                            v-if="byIndexToShow == undefined || data.index <= byIndexToShow"
                            v-bind="data.attrs"
                            close
                            @click:close="remove(data.item)"
                        >
                            {{ data.item.value }}
                        </v-chip>
                        <v-chip
                            v-if="data.index > byIndexToShow && data.index <= byIndexToShow + 1"
                            v-bind="data.attrs"
                            @click="handlerShowAll()"
                        >
                            {{ `+${input.length - (byIndexToShow + 1)}` }}
                        </v-chip>
                    </template>
                    <template v-else>
                        <v-chip v-bind="data.attrs" close @click:close="remove(data.item)">
                            {{ data.item.value }}
                        </v-chip>
                        <v-btn
                            v-if="moreThenOneLine && data.index + 1 === input.length"
                            outlined
                            class="mt-1 mb-1"
                            small
                            v-bind="data.attrs"
                            style="background: none !important"
                            @click="howMuchCanBePlaced()"
                        >
                            Свернуть
                        </v-btn>
                    </template>
                </template>
                <template #item="data">
                    <div class="remote-search-output">
                        {{ data.item.value }}

                        <v-tooltip top>
                            <template #activator="{ on, attrs }">
                                <div
                                    v-if="data.item.popularity && data.item.popularity !== null"
                                    class="remote-search-output__popularity"
                                    v-bind="attrs"
                                    v-on="on"
                                >
                                    {{ data.item.popularity | splitThousands }}
                                </div>
                            </template>
                            <span>популярность значения характеристики</span>
                        </v-tooltip>
                    </div>
                </template>
            </v-autocomplete>
        </template>
        <template v-else>
            <v-combobox
                ref="select"
                v-model="input"
                :class="{ [$style.HasTooltip]: hasTooltip }"
                :items="getResults"
                :search-input.sync="query"
                :rules="rules"
                :label="label"
                :loading="pending"
                class="light-inline"
                dense
                multiple
                chips
                color="#710bff"
                item-text="value"
                item-value="id"
                @focus="handleFocus"
                @blur="firstInt = true"
            >
                <template #selection="data">
                    <v-chip v-bind="data.attrs" close @click:close="remove(data.item)">
                        {{ data.item.value || data.item }}
                        <!--                        {{ data.item.value }}-->
                    </v-chip>
                </template>
                <template #item="data">
                    <div class="remote-search-output">
                        {{ data.item.value }}

                        <v-tooltip top>
                            <template #activator="{ on, attrs }">
                                <div
                                    v-if="data.item.popularity && data.item.popularity !== null"
                                    class="remote-search-output__popularity"
                                    v-bind="attrs"
                                    v-on="on"
                                >
                                    {{ data.item.popularity | splitThousands }}
                                </div>
                            </template>
                            <span>популярность значения характеристики</span>
                        </v-tooltip>
                    </div>
                </template>
            </v-combobox>
        </template>

        <slot name="append-outer" />
    </div>
</template>

<script>
    import { union, debounce } from 'lodash';
    import { mapGetters } from 'vuex';
    import { errorHandler } from '~utils/response.utils';

    export default {
        name: 'SelectRemoteSearch',
        props: {
            autoRow: Boolean,
            noDense: Boolean,
            noOutlined: Boolean,
            objectModel: Boolean,
            value: {
                type: [String, Object, Array],
                default: null,
            },
            items: {
                type: Array,
                default: () => [],
            },
            rules: {
                type: Array,
                default: () => [],
            },
            label: {
                type: String,
                default: '',
            },
            fieldId: {
                type: String,
                default: '',
            },
            dictionarySlug: {
                type: String,
                default: '',
            },
            maxSelected: {
                type: Number,
                default: 0,
            },
            onlyDictionary: {
                type: Boolean,
                default: true,
            },
            dictionarySearchObject: {
                type: String,
                default: '',
            },
            dictionarySearchType: {
                type: [String, null],
                default: null,
            },
            hasTooltip: {
                type: Boolean,
                default: false,
            },
            category: {
                type: [String, null],
                default: null,
            },
            isBoolean: {
                type: [Boolean],
                default: false,
            },
        },
        data() {
            return {
                byIndexToShow: undefined,
                moreThenOneLine: false,
                showAll: true,
                firstInt: true,
                input: null,
                results: [],
                resultsGenerated: [],
                resultsStored: [],
                query: '',
                pending: false,
                nonEvent: false,
                onlyDictionaryLocal: true,
                booleanValues: [
                    { id: 'да', value: 'да' },
                    { id: 'нет', value: 'нет' },
                ],
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            getResults() {
                /* eslint-disable */
                const union2 =
                    this.marketplaceSlug === 'wildberries' && this.isBoolean
                        ? this.booleanValues
                        : union(this.resultsStored, this.resultsGenerated, this.items, this.results);
                if (this.objectModel) {
                    function getObjectFromArray(Array) {
                        const _o = {};

                        Array.forEach(item => {
                            _o[item.id] = item;
                        });

                        return _o;
                    }

                    let itemsWithoutPop = union2.filter(
                        ({ popularity }) => popularity !== undefined
                    );
                    let itemsWithPop = union2.filter(({ popularity }) => popularity);

                    itemsWithoutPop = getObjectFromArray(itemsWithoutPop);
                    itemsWithPop = getObjectFromArray(itemsWithPop);

                    const res = [];
                    // Если ключа нету в массиве с популярностью то вставляем его

                    Object.keys(itemsWithoutPop).forEach(key => {
                        if (!Object.keys(itemsWithPop).includes(itemsWithoutPop[key])) {
                            res.push(itemsWithoutPop[key]);
                        }
                    });

                    Object.keys(itemsWithPop).forEach(key => {
                        res.push(itemsWithPop[key]);
                    });

                    return res;
                }
                // return this.results.length ? this.results : this.items;
                return union2;
            },
            getQueryUrl() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return '/api/vp/v2/wildberries/directories' + this.dictionarySlug;

                    default:
                        return '/api/vp/v2/oz/directories/' + this.fieldId;
                }
            },
            getSearchParams() {
                const params = {};

                this.query && (params.search = this.query);

                if (this.dictionarySearchObject) {
                    params.object = this.dictionarySearchObject;
                }
                if (this.dictionarySearchType) {
                    params.type = this.dictionarySearchType;
                }
                if (this.category !== null) {
                    params.category = this.category;
                }
                return params;
            },
            onlyDictionaryComputed() {
                return Boolean(this.onlyDictionary || this.onlyDictionaryLocal);
            },
            inputChangeTrigger() {
                return JSON.stringify(this.input);
            },
            getResultsLengthTrigger() {
                return this.getResults.length;
            }
        },
        watch: {
            inputChangeTrigger: {
                handler() {
                    if (this.autoRow) this.howMuchCanBePlaced(true);
                    if (this.maxSelected && this.input?.length > this.maxSelected) {
                        this.nonEvent = true;
                        this.input = this.input.slice(-1 * this.maxSelected);
                    }

                    this.$emit('input', this.input || []);
                    if (!this.firstInt && !this.nonEvent) {
                        this.$emit('change');
                    }
                    this.nonEvent = false;
                },
            },
            value() {
                this.setValue();
                this.handleOptionsFromProps();
            },
            query(val) {
                if (val && val.length > 0) {
                    this.fetchDictionaryByQuery();
                } else {
                    // this.results = [];
                    this.pending = false;
                }
            },
            getResultsLengthTrigger() {
                if (this.getResultsLengthTrigger) {
                    this.resultsStored = this.getResults;
                }
            },
        },
        created() {
            this.fetchDictionaryByQuery = debounce(this.fetchDictionaryByQuery, 500);

            this.handleOptionsFromProps();
        },
        async mounted() {
            this.setValue();
            if (this.autoRow) this.howMuchCanBePlaced();
        },

        beforeDestroy() {
            this.onlyDictionaryLocal = true;
        },
        methods: {
            handlerShowAll() {
                this.showAll = true;
                this.moreThenOneLine = true;
                // this.byIndexToShow = undefined;
            },
            customFocus(e) {
                this.handleFocus();
                const freeEntryFields = ['комплектация'];
                const isWb = this.isSelectedMp.id === 2;
                const admitFreeEntry = freeEntryFields.includes(
                    this.$refs.select.label.toLowerCase()
                );

                if (!(isWb && admitFreeEntry)) return;

                e.preventDefault();
                const input = e.target;

                const keyPressEnter = e => {
                    if (!this.showAll) this.showAll = true;
                    const { key } = e;
                    if (key !== 'Enter') return;
                    pasteValue();
                };

                const pasteValue = () => {
                    if (!input.value || this.input.includes(input.value)) return;
                    this.input.push(input.value);
                    this.$refs.select.lazySearch = '';
                };

                input.addEventListener('keypress', keyPressEnter);
                input.addEventListener('blur', e => {
                    pasteValue();
                    input.removeEventListener('keypress', keyPressEnter);
                });
            },
            howMuchCanBePlaced(resetTmpl) {
                try {
                    const { $el: el } = this;
                    const parentEl = el.querySelector('.v-select__selections');
                    let startWidth = 0;
                    let moreThenOneLine = false;

                    const interval = setInterval(() => {
                        const chips = Array.from(el.getElementsByClassName('v-chip'));
                        const parentElWidth = parentEl.offsetWidth;
                        if (parentElWidth !== 0) {
                            clearInterval(interval);
                            for (let i = 0, l = chips.length; i < l; i += 1) {
                                startWidth += chips[i].offsetWidth + 16;
                                if (startWidth > parentElWidth) {
                                    moreThenOneLine = true;
                                    this.byIndexToShow = i == 0 ? i : i - 1;
                                    break;
                                }
                            }
                            this.moreThenOneLine = moreThenOneLine;
                        }
                    }, 1000);
                } catch (error) {
                    console.error(error);
                }
                if (!resetTmpl) this.showAll = false;
            },
            setValue() {
                const value =
                    this.marketplaceSlug === 'wildberries' &&
                    this.isBoolean &&
                    Array.isArray(this.value)
                        ? this.value.map(el => el.toLowerCase())
                        : this.value;
                this.input = value;
            },
            setBooleanValues() {
                this.input = this.booleanValues;
            },
            handleOptionsFromProps() {
                if (this.marketplaceSlug === 'wildberries') {
                    const valuesNotInOptions = this.getValuesNotInOptions();
                    if (valuesNotInOptions.length) {
                        this.setOptionItemFromValues(valuesNotInOptions);
                    }
                }
            },
            getValuesNotInOptions() {
                if (Array.isArray(this.value)) {
                    const result = [];
                    this.value.forEach(valueItem => {
                        const found = this.getResults.find(x => x.value === valueItem.value);
                        if (!found) {
                            result.push(valueItem);
                        }
                    });
                    return result;
                } else {
                    return false;
                }
            },
            setOptionItemFromValues(values) {
                values.forEach(value => {
                    this.$set(
                        this.resultsGenerated,
                        this.resultsGenerated.length,
                        JSON.parse(
                            JSON.stringify({
                                id: value,
                                popularity: null,
                                value,
                            })
                        )
                    );
                });
            },
            fetchDictionaryByQuery() {
                const params = this.getSearchParams;
                this.pending = true;
                return (
                    this.$axios
                        .$get(this.getQueryUrl, { params })
                        // .then(({ data }) => {
                        .then(response => {
                            let resultsResponse;
                            if (this.marketplaceSlug === 'wildberries') {
                                resultsResponse = this.processResults(response.items.data);
                                this.onlyDictionaryLocal = response.useOnlyDictionaryValues;
                            } else {
                                resultsResponse = this.processResults(response.data);
                            }

                            this.results = resultsResponse;
                            if (this.maxSelected !== 1 && !this.resultsStored.length) {
                                this.resultsStored = resultsResponse;
                            } else if (this.resultsStored.length) {
                                const resultsStoredFiltered = this.resultsStored.filter(el => {
                                    return this.input.includes(el.id);
                                });
                                this.resultsStored = resultsStoredFiltered;
                            }

                        })
                        .catch(({ response }) => {
                            errorHandler(response, this.$notify);
                        })
                        .finally(() => {
                            this.pending = false;
                            this.setMenuActive();
                        })
                );
            },
            remove(item) {
                const indexID = this.input.indexOf(item.id);
                const index = indexID >= 0 ? indexID : this.input.indexOf(item);
                if (index >= 0) {
                    const newData = [...this.input];
                    newData.splice(index, 1);

                    this.input = newData;
                }
            },
            processResults(data) {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        // eslint-disable-next-line no-case-declarations
                        const result = [];
                        data.forEach(current => {
                            const item = {
                                id: current.title,
                                value: current.title,
                                popularity: current.popularity,
                            };
                            result.push(item);
                        });
                        return result;

                    default:
                        return data;
                }
            },
            handleFocus() {
                this.fetchDictionaryByQuery();
                this.firstInt = false;
            },
            setMenuActive() {
                this.$refs.select.isFocused = true;
                this.$refs.select.isMenuActive = true;
            },
        },
    };
</script>

<style lang="scss" module>
    .SelectRemoteSearch {
        display: flex;

        & .HasTooltip {
            max-width: calc(100% - 1.5rem - 9px);
        }
    }
</style>
