<template>
    <div class="se-alert" :class="`se-alert--${type}`">
        <SvgIcon
            :name="icon.name"
            class="mr-2"
            :style="{ color: icon.color, 'min-width': '20px', 'min-height': '20px' }"
        />
        <div class="se-alert__text">
            <slot>Текст уведомления</slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            type: {
                type: String,
                default: 'info',
            },
        },
        computed: {
            icon() {
                const nameIcon = {
                    alert: {
                        name: 'filled/warningoutlined',
                        color: '#FF3981',
                    },
                    info: {
                        name: 'filled/info',
                        color: '#607FEA',
                    },
                    success: {
                        name: 'filled/checkoutlined',
                        color: '#20C274',
                    },
                };
                return nameIcon[this.type] || nameIcon.info;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .se-alert {
        display: flex;
        align-items: center;
        padding: 8px;
        border-radius: 8px;
        border-style: solid;
        border-width: 1px;
        font-weight: 500;
        font-size: 14px;

        &--info {
            border-color: #b3c3ff;
        }

        &--alert {
            border-color: #ffb9d2;
        }

        &--success {
            border-color: #20c27446;
        }
    }
</style>
