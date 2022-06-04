<template>
    <div :class="$style.AdmCounterItem">
        <div :class="$style.numbersItemHeading" :title="heading">{{ heading }}</div>
        <VFadeTransition mode="out-in">
            <div :key="`${heading}-${value}`" :class="$style.numbersItemValue" :title="value">
                {{ value | splitThousands }}
            </div>
        </VFadeTransition>

        <LazyVTranding
            v-if="dynamicValue"
            :class="$style.dynamicWrapper"
            :value="dynamicValue"
            :direction-down="dynamicDirectionDown"
        />
    </div>
</template>

<script>
    export default {
        name: 'AdmCounterItem',
        props: {
            heading: {
                type: String,
                default: '',
            },
            value: {
                type: [String, Number],
                default: '',
            },
            dynamicValue: {
                type: [String, Number],
                default: '',
            },
            dynamicDirectionDown: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style lang="scss" module>
    .AdmCounterItem {
        //
    }

    .numbersItemHeading {
        font-size: 1.6rem;
        line-height: percentage(24/16);
        color: $base-800;
        user-select: none;

        @extend %ellipsis;

        @include respond-to(md) {
            font-size: 14px;
        }
    }

    .numbersItemValue {
        font-size: 2.8rem;
        line-height: percentage(38/28);
        color: $base-900;
        font-weight: bold;

        @include respond-to(md) {
            font-size: 18px;
        }

        @extend %ellipsis;
    }

    .dynamicWrapper {
        margin-top: 0.6rem;
    }
</style>
