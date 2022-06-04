<template>
    <Component
        :is="component.is"
        v-model="isShow"
        v-bind="component.bind"
        @keydown.esc="handleClose"
    >
        <div :class="$style.wrapper" @contextmenu.stop.prevent>
            <div v-if="isMobile" :class="$style.headingWrapper">
                <h3 :class="$style.heading">
                    <span :class="$style.headingInner">{{ data.heading }}</span>
                    <span v-if="headingName" :class="$style.headingName">
                        {{ headingName }}
                    </span>
                </h3>
                <VBtn :class="$style.closeButton" fab small outlined @click="handleClose">
                    <SvgIcon name="outlined/close" />
                </VBtn>
            </div>
            <VList :class="$style.list">
                <VListItem
                    v-for="item in $contextMenu.data.items"
                    :key="item.value"
                    :class="$style.item"
                    dense
                    :disabled="item.disabled"
                    v-bind="!!item.route ? { to: item.route } : {}"
                    @click="handleClick(item)"
                >
                    <!-- v-if="isMobile" -->
                    <VListItemIcon :class="$style.iconWrapper">
                        <div :class="$style.iconInner">
                            <SvgIcon :name="item.icon" />
                        </div>
                    </VListItemIcon>
                    <VListItemTitle>{{ item.title }}</VListItemTitle>
                </VListItem>
            </VList>
        </div>
    </Component>
</template>

<script>
    /* eslint-disable no-extra-parens */
    import { VBottomSheet, VMenu } from 'vuetify/lib';
    export default {
        name: 'TheContextMenu',
        components: { VBottomSheet, VMenu },
        data() {
            return {
                show: false,
                scrollArea: null,
            };
        },
        computed: {
            appWidth() {
                return this.$nuxt.$vuetify.breakpoint.width;
            },
            items() {
                return this.$contextMenu.data.items;
            },
            data() {
                return this.$contextMenu.data;
            },
            headingName() {
                if (
                    (!this.data?.item && !this.data?.item?.surname) ||
                    !this.data?.item?.name ||
                    !this.data?.item?.patronymic
                ) {
                    return '';
                }
                return this.$options.filters.initials(
                    this.data.item.surname,
                    this.data.item.name,
                    this.data.item.patronymic
                );
            },
            isMobile() {
                if (!process.client) {
                    return false;
                }
                return this?.$nuxt?.$vuetify.breakpoint.mdAndDown;
            },
            component() {
                if (!this.isMobile) {
                    return {
                        is: 'VMenu',
                        bind: !this.data.options.attach
                            ? {
                                  'position-x': this.data.options.x,
                                  'position-y': this.data.options.y,
                                  absolute: true,
                                  'offset-y': true,
                                  'content-class': 'contextMenu',
                              }
                            : {
                                  attach: this.data.options.attach,
                                  'content-class':
                                      'contextMenu contextMenuAttached' +
                                      (this.data.options.attachTop
                                          ? ' contextMenuAttachedTop'
                                          : ''),
                              },
                    };
                }
                return {
                    is: 'VBottomSheet',
                    bind: {
                        'content-class': 'contextBottomSheet',
                    },
                };
            },
            isShow: {
                get() {
                    return this?.$contextMenu?.data?.show;
                },
                set(val) {
                    this.show = val;
                    this.onChange('show', val);
                    if (!val) {
                        this.$emit('close');
                    }
                },
            },
        },
        watch: {
            appWidth() {
                return this.handleScroll();
            },
        },
        beforeMount() {
            this.$contextMenu.emitter.on('openContextMenu', this.onOpen);
            this.$contextMenu.emitter.on('closeContextMenu', this.onClose);
            this.$contextMenu.emitter.on('changeContextMenu', this.onChange);
            this.scrollArea = document.querySelector('#scroll_area') || window;
            this.scrollArea.addEventListener('scroll', this.handleScroll, {
                passive: true,
            });
        },
        beforeDestroy() {
            this.$contextMenu.emitter.off('openContextMenu', this.onOpen);
            this.$contextMenu.emitter.off('closeContextMenu', this.onClose);
            this.$contextMenu.emitter.off('changeContextMenu', this.onChange);
            this.scrollArea.removeEventListener('scroll', this.handleScroll);
        },
        methods: {
            handleClick(item) {
                this.$contextMenu.emitter.emit(item.value);
                this.$contextMenu.close();
            },
            handleScroll() {
                if (this.isShow) {
                    this.handleClose();
                }
            },
            handleClose() {
                this.isShow = false;
            },
            onOpen({ items, heading, options, item }) {
                this.$contextMenu.change('heading', heading);
                this.$contextMenu.change('options', options);
                this.$contextMenu.change('item', item);
                this.$contextMenu.change('items', items);
                this.$contextMenu.change('show', true);
                this.isShow = true;
                if (items?.length) {
                    for (const it of items) {
                        if (!it.value || !it.callback) {
                            continue;
                        }
                        this.$contextMenu.emitter.on(it.value, it.callback);
                    }
                }
            },
            onClose() {
                if (this?.$contextMenu?.data?.items) {
                    for (const it of this.$contextMenu.data.items) {
                        if (!it.value || !it.callback) {
                            continue;
                        }
                        this.$contextMenu.emitter.off(it.value, it.callback);
                    }
                }
                this.$contextMenu.change('heading', '');
                this.$contextMenu.change('options', {});
                this.$contextMenu.change('item', {});
                this.$contextMenu.change('items', []);
                this.$contextMenu.change('show', false);
                this.isShow = false;
            },
            onChange(prop, val) {
                this.$set(this.data, prop, val);
            },
        },
    };
</script>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .wrapper {
        border-radius: inherit;
        background-color: $white;
    }

    .closeButton {
        position: absolute;
        top: 50%;
        right: var(--gap);
        transform: translateY(-50%);
    }

    .iconInner,
    .iconWrapper {
        width: 20px;
        min-width: 20px;
        height: 20px;

        @include respond-to(md) {
            width: 40px;
            min-width: 40px;
            height: 40px;
        }
    }

    .iconWrapper {
        margin-right: 12px !important;
    }

    .iconInner {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: $base-800;
    }

    .headingWrapper {
        position: relative;
        padding-top: var(--gap);
        padding-right: var(--gap);
        padding-bottom: var(--gap);
        padding-left: var(--gap);

        @include respond-to(sm) {
            padding-top: 24px;
            padding-bottom: 24px;
        }

        @include borderLine();
    }

    .heading {
        padding-right: 36px;
        font-size: 22px;
        color: $base-800;
        font-weight: 500;
        user-select: none;

        @include respond-to(sm) {
            font-size: 18px;
        }
    }

    .list {
        @include respond-to(md) {
            padding-top: 14px;
            padding-bottom: 14px;
        }
    }

    .item {
        justify-content: flex-start !important;
        height: 36px !important;
        min-height: 36px !important;
        padding-right: 16px !important;
        padding-left: 16px !important;
        border-width: 0 !important;
        font-size: 14px !important;
        color: $base-800;
        user-select: none;
        font-weight: 500;

        &:hover {
            background-color: rgba($base-100, 0.4) !important;
        }

        &:not(:global(.v-list-item--disabled)) {
            color: $base-600;

            .iconInner {
                color: $base-800;
            }
        }

        @include respond-to(md) {
            height: 56px !important;
            min-height: 56px !important;
            padding-right: var(--gap) !important;
            padding-left: var(--gap) !important;
        }
    }

    :global(.contextMenu) {
        z-index: 999 !important;

        --list-item-title-font-size: 14px;
    }

    :global(.contextMenuAttached) {
        top: unset !important;
        bottom: 0 !important;
        // top: 0% !important;
        left: -12px !important;
        max-width: unset !important;
        // margin-right: 12px;
        transform: translateX(-100%);
    }

    :global(.contextMenuAttachedTop) {
        top: 0 !important;
        bottom: unset !important;
    }

    :global(.contextBottomSheet) {
        @include respond-to(md) {
            max-width: 90%;
            border-radius: 16px !important;
            border-bottom-right-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        @include respond-to(sm) {
            max-width: 100%;
        }
    }
</style>
