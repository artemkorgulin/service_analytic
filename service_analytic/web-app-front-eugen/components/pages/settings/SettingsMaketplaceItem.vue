<template>
    <v-expansion-panel :disabled="disabled" :class="$style.panel" @click="$emit('expand', item.id)">
        <v-expansion-panel-header :class="$style.mpHeadingWrapper">
            <div :class="$style.mpHeadingInner">
                <VImg :class="$style.mpImg" :src="item.image" />

                <div :class="$style.marketplaceHeading">{{ item.name }}</div>
            </div>
        </v-expansion-panel-header>
        <v-expansion-panel-content>
            <!-- <div  :class="$style.expandedArea"> -->
            <slot></slot>
            <!-- </div> -->
        </v-expansion-panel-content>
    </v-expansion-panel>
</template>

<script>
    export default {
        name: 'SettingsMaketplaceItem',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
            isActive: {
                type: Boolean,
                default: false,
            },
            disabled: Boolean,
        },
        computed: {
            isSlot() {
                return Boolean(this.$slots.default);
            },
        },
    };
</script>

<style lang="scss" module>
    .expandedArea {
        width: 100%;
        // height: 200px;
        // background: red;
        padding: 16px;
    }

    .SettingsMaketplaceItem {
        position: relative;
        // display: flex;
        // align-items: center;
        // height: 65px;
        // padding-right: 16px;
        // padding-left: 16px;
        border-top: 1px solid $base-100;
        border-bottom: 1px solid $base-100;
        color: $base-900;

        &.disabled {
            user-select: none;
            pointer-events: none;
            opacity: 0.7;
        }
    }

    .panel {
        border-bottom: 1px solid $border-color;
    }

    .marketplaceHeading {
        @extend %text-h5;

        margin-left: 16px;
    }

    .mpHeadingInner {
        display: flex;
        align-items: center;
    }

    .mpHeadingWrapper {
        display: flex;
        justify-content: space-between;
        width: 100%;
        padding: 16px;
        // border-bottom: 1px solid $border-color;
    }

    .toggleBtn {
        min-width: 40px !important;
        padding-right: 0 !important;
        padding-left: 0 !important;
    }

    .chevron {
        transition: $primary-transition;

        &.reverce {
            transform: rotate(180deg);
        }
    }

    .mpImg {
        $size: 35px;

        width: $size;
        max-width: $size;
        height: $size;
        max-height: $size;
    }
</style>
