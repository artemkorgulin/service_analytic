<template>
    <VTooltip right content-class="tooltip-with-activator">
        <template #activator="{ on, attrs }">
            <button v-bind="attrs" :class="$style.tooltipButton" v-on="on">
                <SvgIcon name="filled/info" :class="$style.tooltipIcon" />
            </button>
        </template>
        <span>{{ text }}</span>
    </VTooltip>
</template>

<script>
    export default {
        name: 'BaseTooltip',
        props: {
            text: {
                type: String,
                default: '',
            },
            isResponsive: {
                type: Boolean,
                default: false,
            },
        },
    };
</script>

<style lang="scss" module>
    .tooltipWrapper {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    :global(.tooltip-with-activator) {
        background-color: rgba(97, 97, 97, 0.99);
    }

    .tooltipIcon {
        color: #c8cfd9;
    }

    .tooltipButton {
        width: 1.6rem;
        height: 1.6rem;
        margin-right: 0.55rem;

        .tooltipIcon {
            width: 1.6rem;
            height: 1.6rem;
        }
    }
</style>
