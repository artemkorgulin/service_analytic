<template>
    <div :class="$style.TopByPositionsItem">
        <div :class="$style.positionWrapper">
            <div :class="$style.position">
                {{ item.position }}
            </div>
            <div :class="$style.Icon">
                <img :src="marketplaceIcon" />
            </div>
        </div>
        <VImg :class="$style.image" :src="item.photo_url" contain :alt="item.product_name" />
        <AdmProductLink :class="$style.link" :sku="item.sku" :marketplace="item.marketplace" />
        <!-- TODO optimization? -->

        <v-tooltip bottom>
            <template #activator="{ on, attrs }">
                <div :class="$style.progressWrapper" v-bind="attrs" v-on="on">
                    <VProgressLinear
                        :color="progressColor"
                        rounded
                        height="8"
                        :value="item.optimization"
                    />
                </div>
            </template>
            <div :class="$style.popup">
                Показы и продажи товара зависят от степени оптимизации. Чтобы увеличить показатель,
                заполните больше информации о товаре в Контентной и Поисковой оптимизации
            </div>
        </v-tooltip>

        <v-tooltip bottom>
            <template #activator="{ on, attrs }">
                <div :class="[$style.text, $style.name]" v-bind="attrs" v-on="on">
                    {{ item.product_name }}
                </div>
            </template>
            <div :class="$style.popup">{{ item.product_name }}</div>
        </v-tooltip>

        <v-tooltip bottom>
            <template #activator="{ on, attrs }">
                <div :class="$style.text" v-bind="attrs" v-on="on">
                    {{ item.category }}
                </div>
            </template>
            <div :class="$style.popup">{{ item.category }}</div>
        </v-tooltip>
    </div>
</template>

<script>
    export default {
        name: 'TopByPositionsItem',
        props: {
            item: {
                type: Object,
                default: () => ({}),
            },
        },
        computed: {
            marketplaceIcon() {
                if (this.item.marketplace === 'wb') {
                    return '/images/icons/wb.png';
                }
                return '/images/icons/ozon.png';
            },
            progressColor() {
                const data = Math.ceil(Number(this.item.optimization));

                if (data > 84) {
                    return '#63fdaa'; // 'accent';
                } else if (data > 34) {
                    return '#ffc164'; // 'yellow';
                } else {
                    return '#f56094';
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .TopByPositionsItem {
        display: flex;
        align-items: center;
        height: 48px;
        padding-right: 16px;
        padding-left: 16px;
        border-radius: 5px;
    }

    .positionWrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        max-width: 80px;
        flex-basis: 80px;
        margin-right: 2px;
    }

    .position {
        height: 24px;
        padding-right: 8px;
        padding-left: 8px;
        border-radius: 9999px;
        background-color: $base-100;
        text-align: center;
        line-height: 24px;
    }

    .image {
        width: 32px;
        max-width: 32px;
        height: 32px;
        margin-right: 12px;
    }

    .link {
        max-width: 94px;
        margin-right: 10px;
        flex-basis: 94px;
    }

    .text {
        max-width: 310px;
        flex-basis: 310px;
        padding-right: 16px;
        padding-left: 16px;
        font-size: 14px;
        line-height: 1.2;
        color: #710bff;
        font-weight: 500;

        @extend %ellipsis;

        // &.name {
        //     margin-left: 16px;
        // }
    }

    .popup {
        max-width: 300px;
    }

    .progressWrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 104px;
        padding-right: 16px;
        padding-left: 16px;
        flex-basis: 104px;
    }

    .Icon {
        display: block;
        width: 24px;
        height: 24px;

        img {
            width: 100%;
            object-fit: cover;
        }
    }
</style>
