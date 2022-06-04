<template>
    <div :class="$style.AdmKeywordsGoodsPicker">
        <div :class="$style.wrapper">
            <div class="flex-wrapper">
                <BaseTableHeadingCell
                    inner-text="Товары"
                    :class="$style.headingCell"
                    is-flex
                    is-border-bottom
                />
            </div>
            <div ref="wrapper" :class="$style.scrollAreaWrapper">
                <component :is="componentData.is" ref="scrollArea" :class="$style.tableBody">
                    <BaseTableRow
                        v-for="item in itemsWithRoutes"
                        :key="item.id"
                        :value="String($route.query.picked_id) === String(item.id)"
                        :class="$style.tableRow"
                        is-radio
                        is-flex
                        tag="NuxtLink"
                        :props-to-component="{ to: item.route }"
                    >
                        <BaseTableCell is-flex :class="$style.cell">
                            <div :class="$style.nameWrapper">
                                <div :class="$style.name">
                                    <template v-if="!item.name">Нет названия</template>
                                    <template v-else>
                                        <span
                                            v-if="isSearch"
                                            v-html="genFilteredText(item.name, search)"
                                        />
                                        <template v-else>{{ item.name }}</template>
                                    </template>
                                </div>
                                <VBadge
                                    v-if="item.isGroup"
                                    overlap
                                    :value="item.goods.length"
                                    :content="item.goods.length"
                                    color="base-800"
                                >
                                    <div :class="$style.goodsCount">
                                        <SvgIcon name="filled/folderclosed" />
                                    </div>
                                </VBadge>
                            </div>
                        </BaseTableCell>
                    </BaseTableRow>
                </component>
            </div>
        </div>
    </div>
</template>

<script>
    import { defineComponent } from '@nuxtjs/composition-api';

    import { genFilteredText } from '~utils/helpers';
    export default defineComponent({
        name: 'AdmKeywordsGoodsPicker',
        props: {
            items: {
                type: Array,
                default: () => [],
            },
            isSearch: {
                type: Boolean,
                default: false,
            },
            search: {
                type: String,
                default: '',
            },
        },
        data() {
            return {
                innerHeight: '100%',
            };
        },
        computed: {
            itemsWithRoutes() {
                return this.items.map(item => {
                    const copy = { ...item };
                    copy.route = {
                        name: 'campaign-keywords',
                        params: this.$route.params,
                        query: {
                            picked_id: item.id,
                        },
                    };
                    return copy;
                });
            },
            styles() {
                return {
                    height: this.innerHeight + 'px',
                };
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
        mounted() {
            this.isMounted = true;
        },
        methods: {
            genFilteredText,
        },
    });
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .AdmKeywordsGoodsPicker {
        width: 100%;
    }

    .scrollAreaWrapper {
        overflow: hidden;
        display: flex;
        flex: 1 1 100%;
        width: 100%;
    }

    .tableBody {
        overflow: auto;
        display: table-row-group;
        width: 100%;
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

    .wrapper {
        overflow: hidden;
        display: flex;
        width: 100%;
        height: 100%;
        flex-direction: column;
    }

    .headingCell {
        width: 100%;
        max-width: 100%;
        min-height: 5.6rem;
    }

    .tableRow {
        min-height: 5.6rem;
        text-decoration: none;
        color: $base-900;

        @include respond-to(md) {
            min-height: 40px;
        }
    }

    .cell {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }

    .nameWrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 100%;

        .name {
            @extend %text-body-1;
            @extend %ellipsis;

            font-size: 1.6rem;

            @include respond-to(md) {
                font-size: 14px;
            }
        }

        .goodsCount {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            background-color: $base-400;

            .goodsCountInner {
                @extend %text-body-2;

                color: $base-900;
            }
        }
    }
</style>
