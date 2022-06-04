<template>
    <BaseTableRow :class="classes" is-flex @contextmenu.prevent.native="handleContextMenu">
        <template #prepend>
            <AdmCheckboxCell :value="selected" @click.native="handleKeywordToggle(item)" />
        </template>
        <BaseTableCell width="calc(100% - 16.8rem - 12rem)" is-flex>
            <AdmKeywordsSplit
                :value="item.name"
                :list="stopwords"
                @addStopword="chunk => $emit('addStopword', chunk)"
            />
        </BaseTableCell>
        <BaseTableCell
            width="16.8rem"
            is-flex
            :inner-text="!!item.popularity ? String(item.popularity) : ''"
        />
        <BaseTableCell width="12rem" :inner-text="!!item.bid ? String(item.bid) : ''" is-flex />
        <template #append>
            <BaseTableCell
                v-ripple
                is-active
                is-flex
                :class="$style.cellAction"
                @click.native="handleKeywordDelete(item)"
            >
                <SvgIcon :class="$style.action" name="outlined/deletetrash" />
            </BaseTableCell>
        </template>
    </BaseTableRow>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        name: 'AdmKeywordsTableItem',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            selected: {
                type: Boolean,
                default: false,
            },
            highlight: {
                type: Boolean,
                default: false,
            },
            headings: {
                type: Array,
                default: () => [],
            },
        },
        computed: {
            ...mapGetters('keywords', {
                stopwords: 'getMinusWordsNames',
            }),
            classes() {
                return [
                    this.$style.AdmKeywordsTableItem,
                    {
                        [this.$style.selected]: this.selected && !this.highlight,
                        [this.$style.highlight]: this.highlight,
                    },
                ];
            },
            nameSplitted() {
                if (!this.item.name) {
                    return [];
                }
                const splitRes = this.item.name.split(' ');
                if (splitRes.length === 1) {
                    return [];
                }
                return splitRes;
            },
        },
        methods: {
            handleKeywordToggle(payload) {
                return this.$emit('toggle', payload);
            },
            handleKeywordDelete(payload) {
                return this.$emit('delete', payload);
            },
            handleContextMenu(e) {
                return this.$emit('context', {
                    attach: false,
                    x: e.clientX,
                    y: e.clientY,
                    item: this.item,
                });
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AdmKeywordsTableItem {
        min-height: var(--size);
        transition: $primary-transition;
        transition-property: background-color;

        --size: 5.6rem;

        @include respond-to(md) {
            --size: 40px;
        }

        @include respond-to(md) {
            min-height: 40px;
        }

        &.selected {
            background-color: rgba($accent, 0.03);
        }

        &.highlight {
            background-color: rgba($accent, 0.07);
        }
    }

    .cellAction {
        justify-content: center;
        min-width: var(--size);
        min-height: var(--size);
        padding-top: unset !important;
        padding-bottom: unset !important;

        &.disabled {
            opacity: 0.7;
            pointer-events: none;
            user-select: none;
        }

        // @include respond-to(md) {
        //     min-width:  var(--size);
        // }
    }

    .action {
        $action-size: 2rem;

        width: $action-size !important;
        max-width: $action-size !important;
        height: $action-size !important;
        min-height: $action-size !important;
        max-height: $action-size !important;
        color: $base-700;
    }
</style>
