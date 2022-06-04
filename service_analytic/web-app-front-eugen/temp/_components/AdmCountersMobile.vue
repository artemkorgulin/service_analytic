<template>
    <component
        :is="componentIs.is"
        :class="[$style.AdmCountersMobile, componentIs.isDesktop ? $style.desktop : $style.mobile]"
    >
        <div v-for="(value, key) in dictionary" :key="`statistics-count-item-${key}`">
            <AdmCounterItem :class="$style.numbersItem" :heading="value" :value="items[key]" />
        </div>
    </component>
</template>

<script>
    export default {
        name: 'AdmCountersMobile',
        props: {
            items: {
                type: Object,
                default: () => ({}),
            },
            dictionary: {
                type: Object,
                default: () => ({}),
            },
        },
        computed: {
            componentIs() {
                return this.$nuxt.$device.isDesktop
                    ? {
                          is: 'PerfectScrollbar',
                          isDesktop: true,
                      }
                    : {
                          is: 'div',
                          isDesktop: false,
                      };
            },
        },
    };
</script>

<style lang="scss" module>
    .AdmCountersMobile {
        display: flex;
        background-color: $white;

        &.mobile {
            overflow-x: auto;
        }

        :global(.ps__thumb-x) {
            max-height: 6px;
        }

        :global(.ps__rail-x) {
            height: 10px;
        }
    }

    .numbersItem {
        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        flex: 1 1 auto;
        max-width: 157px;
        padding: 16px;
        background-color: $white;
        flex-direction: column;

        @include respond-to(md) {
            padding: 15px 20px;
        }

        &:after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            display: block;
            width: 1px;
            height: 100%;
            background-color: $base-400;
        }
    }
</style>
