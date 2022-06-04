<template>
    <div class="brand-selector" @mouseover="hover = true" @mouseleave="hover = false">
        <div v-if="!addBrand" class="brand-selector__side-space" :class="{ 'brand-selector__side-space_hover': hover }"></div>
        <VSelect
            v-model="selectValue"
            class="brand-selector__select"
            :style="selectStyle"
            :items="itemsFiltered"
            :search-input.sync="query"
            :item-text="itemText"
            :item-value="itemValue"
            :loading="pending"
            :label="label || null"
            dense
            flat
            color="#710bff"
            append-icon="$expand"
            @change="
                handleChange({
                    value: selectValue,
                    index,
                })
            "
        >
            <template #prepend-item>
                <v-list-item>
                    <v-list-item-content>
                        <SearchInput v-model="search" color="#710bff" label="Поиск" />
                    </v-list-item-content>
                </v-list-item>
                <v-divider class="mt-2"></v-divider>
            </template>
        </VSelect>
        <div v-if="!addBrand" class="brand-selector__side-space" :class="{ 'brand-selector__side-space_hover': hover }"></div>
        <template v-if="!addBrand && hover">
            <VFadeTransition mode="out-in" appear>
                <v-btn class="brand-selector__delete-brand" fab icon small @click="handleRemoveBrand">
                    <SvgIcon name="outlined/close" />
                </v-btn>
            </VFadeTransition>
        </template>

    </div>
</template>

<script>
    import Vue from 'vue';
    import { mapActions } from 'vuex';

    export default Vue.extend({
        name: 'AgCustomCellSelect',
        data() {
            return {
                index: null,
                items: null,
                label: null,
                selectValue: null,
                search: '',
                query: '',
                pending: false,
                itemText: null,
                itemValue: null,
                addBrand: false,
                hover: false,
                widthHardcoded: {
                    max: 280,
                    min: 70,
                },
            };
        },
        computed: {
            itemsFiltered() {
                return this.items?.filter(post =>
                    post && post[this.itemText]?.toLowerCase().includes(this.search.toLowerCase())
                ) || [];
            },
            selectStyle() {
                // TODO: Придумать более красивый способ ужимать ширину селекта при коротком выбранном значении
                const textLength = this.itemsFiltered.find(el => el.brand_id === this.selectValue)?.brand?.length;
                const computedWidth = textLength * 10 + 40;
                const getWidth = () => {
                    if (this.addBrand) {
                        return '160px';
                    } else if (computedWidth < this.widthHardcoded.min) {
                        return `${this.widthHardcoded.min}px`;
                    } else if (computedWidth >= this.widthHardcoded.max) {
                        return '100%';
                    } else {
                        return `${computedWidth}px`;
                    }
                };

                return {
                    width: getWidth(),
                };
            },
        },
        watch: {
            search(val) {
                if (val && val.length > 1) {
                    this.fetchDictionaryByQuery();
                } else {
                    this.pending = false;
                }
            },
        },
        created() {
            this.index = this.params.index || 0;
            this.items = this.params.items || [];
            this.label = this.params.label || null;
            this.selectValue = this.params.selectValue || null;
            this.itemText = this.params.itemText || 'brand';
            this.itemValue = this.params.itemValue || 'brand_id';
            this.addBrand = this.params.addBrand || false;
        },
        methods: {
            ...mapActions('category-analysis', ['updateBrandsTable', 'removeBrand']),
            async fetchDictionaryByQuery() {
                this.pending = true;
                try {
                    const { data } = await this.$axios.$get(
                        '/api/an/v1/wb/statistic/brands/find?filter=' + this.search
                    );
                    this.items = Object.values(data);
                    this.pending = false;
                } catch (error) {
                    console.error('🚀 fetchDictionaryByQuery ~ error', error);
                }
            },
            handleChange(payload) {
                this.search = '';
                this.query = '';
                this.updateBrandsTable({
                    brand_id: payload.value,
                    index: this.index,
                });
            },
            handleRemoveBrand(payload) {
                this.search = '';
                this.query = '';
                this.removeBrand({
                    index: this.index,
                });
            },
        },
    });
</script>

<style lang="scss" scoped>
    /* stylelint-disable selector-pseudo-element-no-unknown */
    .brand-selector {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        padding: 4px 15px 0;
        transition: background-color 150ms ease-in;

        &:hover {
            background-color: $base-500;
        }

        &__select {
            flex-grow: 0;
        }

        &__side-space {
            width: 0;
            transition: width 200ms ease-in;

            &_hover {
                width: 15px;
            }
        }

        &::v-deep  .brand-selector__delete-brand {
            position: absolute;
            top: 50%;
            right: 15px;
            width: 9px;
            height: 9px;
            transform: translateY(-50%);

            &::v-deep .v-btn__content {
                width: 16px;
            }
        }

        &::v-deep .v-text-field > .v-input__control > .v-input__slot:before,
        &::v-deep .v-select.v-input--dense .v-select__selection--comma {
            border-style: none !important;
        }
    }
</style>
