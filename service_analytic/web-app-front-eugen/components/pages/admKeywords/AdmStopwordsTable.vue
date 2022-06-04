s
<template>
    <div :class="$style.AdmEditStopwords">
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
                    :disabled="!isSelectAllStopwordsEnabled"
                    :value="isAllStopwordsSelected"
                    :indeterminate="isAnyStopwordSelected && !isAllStopwordsSelected"
                    @click.native="handleToggleAllStopwords"
                />
                <BaseTableHeadingCell
                    v-for="heading in headings"
                    :key="heading.value"
                    :class="$style.headingCell"
                    :inner-text="heading.text"
                    :width="heading.width"
                    is-border-bottom
                    is-flex
                />
                <BaseTableHeadingCell
                    v-ripple="isAnyStopwordSelected"
                    :class="[
                        $style.cellAction,
                        $style.deleteAll,
                        !isAnyStopwordSelected && $style.disabled,
                    ]"
                    is-border-bottom
                    is-border-left
                    is-flex
                    @click.native="handleDeleteAll"
                >
                    <SvgIcon :class="$style.action" name="outlined/deletetrash" />
                </BaseTableHeadingCell>
            </div>
            <div ref="wrapper" :class="$style.scrollAreaWrapper">
                <component :is="componentData.is" ref="scrollArea" :class="$style.tableBody">
                    <template v-if="items.length">
                        <BaseTableRow
                            v-for="item in items"
                            :key="item.id"
                            :class="$style.tableRow"
                            is-flex
                        >
                            <template #prepend>
                                <AdmCheckboxCell
                                    :value="!!selectedReducedById[item.id]"
                                    @click.native="handleToggleStopword(item)"
                                />
                            </template>
                            <BaseTableCell
                                v-for="heading in headings"
                                :key="item.id + heading.value"
                                :width="heading.width"
                                is-flex
                                :inner-text="
                                    isSet(item[heading.value]) ? String(item[heading.value]) : ''
                                "
                            />
                            <template #append>
                                <BaseTableCell
                                    v-ripple
                                    is-active
                                    is-flex
                                    :class="$style.cellAction"
                                    @click.native="$emit('delete', item)"
                                >
                                    <SvgIcon :class="$style.action" name="outlined/deletetrash" />
                                </BaseTableCell>
                            </template>
                        </BaseTableRow>
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
    /* eslint-disable no-unused-vars */
    import { defineComponent } from '@nuxtjs/composition-api';
    import { mapGetters, mapState, mapActions } from 'vuex';
    import { isSet } from '~utils/helpers';

    export default defineComponent({
        name: 'AdmStopwordsTable',

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
        },
        computed: {
            ...mapGetters('keywords', {
                isAnyStopwordSelected: 'isAnyStopwordSelected',
                isAllStopwordsSelected: 'isAllStopwordsSelected',
                isSelectAllStopwordsEnabled: 'isSelectAllStopwordsEnabled',
                selectedReducedById: 'selectedStopwordsReducedById',
            }),
            ...mapState('keywords', {
                selected: state => state.selectedStopwords,
            }),
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
                handleToggleStopword: 'toggleStopword',
                handleToggleAllStopwords: 'toggleAllStopwords',
                handleSelectAllStopwords: 'selectAllStopwords',
                handleUnselectAllStopwords: 'unselectAllStopwords',
            }),
            isSet,
            handleDeleteAll() {
                return this.$emit('deleteAll', this.selected);
            },
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AdmEditStopwords {
        position: relative;
        width: 100%;
        height: 100%;

        --size: 5.6rem;

        @include respond-to(md) {
            --size: 40px;
        }
    }

    .headingCell {
        min-height: var(--size);
    }

    .wrapper {
        overflow: hidden;
        display: flex;
        width: 100%;
        height: 100%;
        flex-direction: column;
    }

    .scrollAreaWrapper {
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

    .tableRow {
        min-height: var(--size);
    }

    .loadingWrapper {
        position: absolute;
        bottom: 16px;
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
