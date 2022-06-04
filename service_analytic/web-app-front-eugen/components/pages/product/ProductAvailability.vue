<template>
    <div class="product-availability">
        <div class="product-availability__title">Наличие</div>
        <div v-if="isSelectedMp.id === 1" class="product-availability__fb-wrapper">
            <div v-for="(el, i) in ozonData" :key="i" class="product-availability__fb-element">
                <div v-if="Number(el.id)">
                    <a
                        :href="el.url"
                        target="_blank"
                        class="product-availability__fb-element-article"
                    >
                        {{ el.id }}
                        <SvgIcon name="outlined/link" class="product-preview__sku-icon" />
                    </a>
                    <VChip
                        label
                        :color="el.bgColor"
                        :text-color="el.color"
                        small
                        style="font-weight: 700"
                    >
                        {{ el.label }}
                        <SvgIcon
                            v-if="el.icon"
                            :name="`filled/${el.icon}`"
                            style="margin-left: 8px"
                        />
                    </VChip>
                </div>
            </div>
        </div>
        <div v-else class="product-availability__fb-wrapper">
            <div class="product-availability__fb-element">
                <VChip
                    label
                    :color="wbData.bgColor"
                    :text-color="wbData.color"
                    small
                    style="font-weight: 700"
                >
                    {{ wbData.label }}
                    <SvgIcon
                        v-if="wbData.icon"
                        :name="`filled/${wbData.icon}`"
                        style="margin-left: 8px"
                    />
                </VChip>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            sku: {
                type: [Object, String],
                default: () => ({}),
            },
            url: {
                type: String,
                default: '',
            },
            quantity: {
                type: [Object, String, Number],
                default: () => ({}),
            },
        },
        computed: {
            ...mapGetters('product', ['getQuantityProduct']),
            ...mapGetters(['isSelectedMp']),
            wbData() {
                return {
                    color: this.getQuantityProduct ? '#20c274' : '#fc6e90',
                    bgColor: this.getQuantityProduct
                        ? 'rgba(32, 194, 116, 0.08)'
                        : 'rgba(255, 11, 153, 0.08)',
                    icon: this.getQuantityProduct ? 'check' : 'close',
                    label: this.getQuantityProduct ? 'на складе' : 'нет на складе',
                };
            },
            ozonData() {
                return Object.keys(this.sku).map(key => ({
                    id: this.sku[key],
                    url: this.url,
                    color: this.quantity[key] ? '#20c274' : '#fc6e90',
                    bgColor: this.quantity[key]
                        ? 'rgba(32, 194, 116, 0.08)'
                        : 'rgba(255, 11, 153, 0.08)',
                    icon: this.quantity[key] ? 'check' : 'close',
                    label: key.toUpperCase(),
                }));
            },
        },
    };
</script>

<style lang="scss" scoped>
    .product-availability {
        display: flex;
        flex-grow: 1;
        flex-direction: column;
        flex-basis: 50%;
        height: 95px;
        padding: 8px 16px;
        border-radius: 8px;
        background-color: #f9f9f9;

        &__title {
            font-size: 12px;
            font-weight: 500;
            line-height: 16px;
            color: #7e8793;
        }

        &__fb-wrapper {
            display: flex;
            gap: 16px;
            margin-top: 8px;
        }

        &__fb-element {
            color: #7e8793;

            &-article {
                overflow: hidden;
                display: flex;
                align-items: center;
                gap: 6px;
                width: 100%;
                margin-bottom: 2px;
                text-decoration: none;
                line-height: 1;
                color: inherit;

                &:hover {
                    color: #710bff;
                }
            }
        }

        &__stock-wrapper {
            margin-top: 8px;
        }
    }
</style>
