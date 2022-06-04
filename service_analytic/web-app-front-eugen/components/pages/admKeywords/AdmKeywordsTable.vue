<template>
    <div :class="$style.AdmEditKeywordsTable">
        <VFadeTransition mode="out-in" appear>
            <div v-if="loading" :class="$style.loadingWrapper">
                <div key="loading" :class="$style.loadingInner">
                    <VProgressCircular indeterminate size="65" color="accent" />
                </div>
            </div>
        </VFadeTransition>
        <div :class="$style.wrapper">
            <div class="flex-wrapper">
                <AdmCheckboxCell
                    :value="isAllKeywordsSelected"
                    :indeterminate="isAnyKeywordSelected && !isAllKeywordsSelected"
                    :disabled="!isSelectAllKeywordsEnabled"
                    @click.native="handleToggleAllKeywords"
                />
                <BaseTableHeadingCell
                    v-for="heading in headings"
                    :key="heading.value"
                    :class="$style.headingCell"
                    :inner-text="heading.text"
                    :width="heading.width"
                    is-border-bottom
                    is-border-right
                    is-flex
                />
                <BaseTableHeadingCell
                    v-ripple="isAnyKeywordSelected"
                    :class="[
                        $style.cellAction,
                        $style.deleteAll,
                        !isAnyKeywordSelected && $style.disabled,
                    ]"
                    is-border-bottom
                    is-flex
                    @click.native="handleDeleteAll"
                >
                    <SvgIcon :class="$style.action" name="outlined/deletetrash" />
                </BaseTableHeadingCell>
            </div>
            <div ref="wrapper" :class="$style.table">
                <component :is="componentData.is" ref="scrollArea" :class="$style.tableBody">
                    <template v-if="items.length">
                        <AdmKeywordsTableItem
                            v-for="item in items"
                            :key="item.id"
                            :item="item"
                            :selected="!!selectedReducedById[item.id]"
                            :highlight="highlightedElementsComputed.includes(String(item.id))"
                            :headings="headings"
                            @delete="handleDelete"
                            @toggle="handleToggleKeyword"
                            @context="options => handleContextMenu({ options, item })"
                            @addStopword="val => $emit('addStopword', val)"
                        />
                    </template>
                    <div v-else :class="$style.empty">
                        <ErrorBanner small img-size="80" />
                    </div>
                </component>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters, mapState, mapActions } from 'vuex';
    import { defineComponent } from '@nuxtjs/composition-api';
    import { isSet } from '~utils/helpers';
    export default defineComponent({
        name: 'AdmKeywordsTable',
        props: {
            items: {
                type: Array,
                default: () => [],
            },
            loading: {
                type: Boolean,
                default: false,
            },
            headings: {
                type: Array,
                default: () => [],
            },
            value: {
                type: String,
                default: '',
            },
            highlightElements: {
                type: Array,
                default: () => [],
            },
        },
        computed: {
            ...mapGetters('keywords', {
                isAnyKeywordSelected: 'isAnyKeywordSelected',
                isAllKeywordsSelected: 'isAllKeywordsSelected',
                isSelectAllKeywordsEnabled: 'isSelectAllKeywordsEnabled',
                selectedReducedById: 'selectedReducedById',
            }),
            ...mapState('keywords', {
                selected: state => state.selectedKeywords,
            }),
            isContextMenuOpen() {
                return this?.$contextMenu?.data?.show;
            },
            highlightedElementsComputed() {
                if (!this.isContextMenuOpen || !this.highlightElements?.length) {
                    return [];
                }
                return this.highlightElements.map(item => String(item.id));
            },
            isEnableScrollbar() {
                return this.items.length > 3 && this.$nuxt.$device.isDesktop;
            },
            componentData() {
                return {
                    is: this.isEnableScrollbar ? 'PerfectScrollbar' : 'div',
                };
            },
        },
        methods: {
            ...mapActions('keywords', {
                handleToggleKeyword: 'toggleKeyword',
                handleToggleAllKeywords: 'toggleAllKeywords',
                handleSelectAllKeywords: 'selectAllKeywords',
                handleUnselectAllKeywords: 'unselectAllKeywords',
            }),
            isSet,
            handleDelete(payload) {
                return this.$emit('delete', payload);
            },
            handleContextMenu(payload) {
                return this.$emit('context', payload);
            },
            handleDeleteAll() {
                return this.$emit('deleteAll', this.selected);
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    .headingCell {
        min-width: var(--size);
        min-height: var(--size);
    }

    .AdmEditKeywordsTable {
        --size: 5.6rem;

        position: relative;
        width: 100%;
        height: 100%;

        @include respond-to(md) {
            --size: 40px;
        }
    }

    .wrapper {
        overflow: hidden;
        display: flex;
        width: 100%;
        height: 100%;
        flex-direction: column;
    }

    .table {
        overflow: hidden;
        display: flex;
        flex: 1 1 100%;
        width: 100%;
    }

    .tableBody {
        display: table-row-group;
        width: 100%;
    }

    .cellAction {
        justify-content: center;
        min-width: var(--size);
        max-width: var(--size);
        min-height: var(--size);
        padding-top: unset !important;
        padding-bottom: unset !important;
        transition: $primary-transition;
        transition-property: background-color;
        cursor: pointer;

        &:hover {
            background-color: $base-400;
        }

        &.deleteAll {
            color: $error;

            .action {
                color: $error;
            }
        }

        &.disabled {
            opacity: 0.7;
            pointer-events: none;
            user-select: none;

            .action {
                color: $base-700;
            }
        }

        @include respond-to(md) {
            min-width: 40px;
        }
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

    .loadingWrapper {
        position: absolute;
        z-index: 11;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.5);

        .loadingInner {
            @include centerer;
        }
    }

    .empty {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1;
        width: 100%;
        height: 100%;
    }
</style>
