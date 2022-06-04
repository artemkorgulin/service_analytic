<template>
    <div id="autocomplete-wrapper" class="autocomplete-search-wrapper">
        <VAutocomplete
            v-model="model"
            :search-input.sync="internalValue"
            :items="items"
            :loading="isLoading"
            :item-text="itemTextFunction"
            :item-value="val => val"
            cache-items
            label="Поиск по адресу"
            prepend-inner-icon="$search"
            :auto-select-first="false"
            hide-no-data
            @change="handleChange"
        >
            <template #selection="{ attr, item }">
                <div v-bind="attr">
                    {{ item.data.inn }}
                    <span>({{ item.value }})</span>
                </div>
            </template>
        </VAutocomplete>
    </div>
</template>

<script>
    export default {
        name: 'BaseAutocomplete',
        props: {
            value: {
                type: String,
                default: '',
            },
            items: {
                type: Array,
                default: () => [],
            },
        },
        data() {
            return {
                isLoading: false,
                model: null,
            };
        },
        computed: {
            itemsMapped() {
                return this.items;
            },
            internalValue: {
                get() {
                    return this.value;
                },
                set(val) {
                    return this.$emit('input', val);
                },
            },
        },
        methods: {
            handleChange(val) {
                console.log('🚀 ~ file: BaseAutocomplete.vue ~ line 56 ~ handleChange ~ val', val);
                return this.$emit('changed', val);
            },
            itemTextFunction(val) {
                console.log(
                    '🚀 ~ file: BaseAutocomplete.vue ~ line 84 ~ itemTextFunction ~ val',
                    val
                );
                return val.value;
            },
        },
    };
</script>
<style lang="scss">
    /* stylelint-disable declaration-no-important */
    .autocomplete-search-wrapper {
        position: relative;
    }

    .autocomplete-search {
        top: unset !important;
        bottom: 0;
        transform: translateY(100%);
        font-weight: 500;

        .v-list-item__title {
            color: $base-900;
        }

        .v-list-item--highlighted {
            .v-list-item__title {
                color: $accent;
            }
        }
    }
</style>
