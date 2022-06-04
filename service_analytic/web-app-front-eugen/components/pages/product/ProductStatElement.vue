<template>
    <div class="product-stat__element">
        <div class="product-stat__element-title">
            {{ el.title }}
        </div>
        <div class="product-stat__element-values">
            <div class="product-stat__element-value">
                {{ value }}
            </div>
            <div
                v-if="el.change && el.change !== 0"
                :class="changeClass"
                class="product-stat__element-change"
            >
                {{ changeValue }}
                <img :src="changeClassIcon" />
            </div>
        </div>
        <div class="product-stat__element-label">
            {{ el.label }}
        </div>
        <div class="product-stat__element-icon-wrapper">
            <img
                class="product-stat__element-icon"
                :src="require(`~/assets/sprite/svg/common/product/${el.iconName}.svg`)"
            />
        </div>
    </div>
</template>

<script>
    export default {
        name: 'ProductStatElement',
        props: {
            el: {
                type: Object,
                default: () => ({}),
            },
        },
        computed: {
            changeClass() {
                return {
                    'product-stat__element-change--up': this.el.change > 0,
                    'product-stat__element-change--down': this.el.change < 0,
                };
            },
            changeClassIcon() {
                return this.el.change > 0 ? '/images/changeUp.svg' : '/images/changeDown.svg';
            },
            changeValue() {
                return this.el.change.toLocaleString();
            },
            value() {
                let value = this.el.value;
                if (value) {
                    value = this.el.value.toLocaleString();
                }
                return value;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .product-stat__element {
        position: relative;
        overflow: hidden;
        flex-grow: 1;
        width: 250px;
        max-width: 50%;
        height: 80px;
        padding: 4px 16px;
        border-radius: 8px;
        background-color: #f9f9f9;

        &-title {
            font-size: 12px;
            font-weight: 500;
            line-height: 16px;
            color: #7e8793;
        }

        &-values {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        &-value {
            font-size: 28px;
            font-weight: 500;
            line-height: 38px;
            color: #2f3640;
        }

        &-change {
            display: flex;
            align-items: center;
            height: 24px;
            margin-top: 2px;
            margin-left: 8px;
            padding: 0 10px;
            border-radius: 4px;
            background: rgba(32, 194, 116, 0.2);
            font-size: 12px;
            line-height: 1;
            color: $color-green-secondary;
            font-weight: 700;

            &--up {
                background-color: rgba(32, 194, 116, 0.06);
                color: $color-green-secondary;
            }

            &--down {
                background-color: rgba(255, 57, 129, 0.06);
                color: $color-red-secondary;
            }

            img {
                width: 6px;
                margin-left: 6px;
            }
        }

        &-label {
            margin-top: 2px;
            font-size: 10px;
            font-weight: 700;
            line-height: 14px;
            color: #7e8793;
        }

        &-icon-wrapper {
            position: absolute;
            top: 33px;
            right: -11px;
            height: 63px;
        }

        &-icon {
            width: 100%;
            height: 100%;
        }
    }
</style>
