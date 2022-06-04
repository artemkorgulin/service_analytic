<template>
    <div :class="$style.AdmCountersDesktop">
        <AdmCounterItem
            v-for="(value, key) in dictionary"
            :key="`statistics-count-item-${key}`"
            :class="$style.numbersItem"
            :heading="value"
            :value="items[key]"
        />
    </div>
</template>

<script>
    export default {
        name: 'AdmCountersDesktop',
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
    };
</script>

<style lang="scss" module>
    .AdmCountersDesktop {
        @extend %sheet;

        display: flex;
        flex-wrap: wrap;
        margin-bottom: $page-gap;
        border-radius: 2.4rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);

        @include respond-to(md) {
            border-radius: 0;
        }
    }

    .numbersItem {
        $items-count-desktop: 6;

        position: relative;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        max-width: percentage(1 / $items-count-desktop);
        padding: 2.4rem;
        flex-basis: percentage(1 / $items-count-desktop);
        flex-direction: column;

        @include respond-to(md) {
            padding: 15px 20px;
        }

        &:after {
            content: '';
            position: absolute;
            top: 24px;
            right: 0;
            bottom: 24px;
            display: block;
            width: 1px;
            height: calc(100% - 48px);
            background-color: $base-400;
        }

        &:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 24px;
            width: calc(100% - 48px);
            height: 1px;
            background-color: $base-400;
        }

        &:nth-child(#{$items-count-desktop}n) {
            &:after {
                display: none;
            }
        }

        &:nth-child(n + #{$items-count-desktop + 1}) {
            &:before {
                display: none;
            }
        }

        @include respond-to(md) {
            max-width: percentage(1/4);
            flex-basis: percentage(1/4);
            &:nth-child(#{$items-count-desktop}n) {
                &:after {
                    display: block;
                }
            }
            &:nth-child(n + #{$items-count-desktop + 1}) {
                &:before {
                    display: block;
                }
            }

            &:nth-child(4n) {
                &:after {
                    display: none;
                }
            }

            &:nth-last-child(-n + 4) {
                &:before {
                    display: none;
                }
            }
        }

        @include respond-to(sm) {
            max-width: percentage(1/2);
            flex-basis: percentage(1/2);

            &:nth-child(4n) {
                &:after {
                    display: block;
                }
            }

            &:nth-last-child(-n + 4) {
                &:before {
                    display: block;
                }
            }

            &:nth-child(2n) {
                &:after {
                    display: none;
                }
            }

            &:nth-last-child(-n + 2) {
                &:before {
                    display: none;
                }
            }
        }
    }
</style>
