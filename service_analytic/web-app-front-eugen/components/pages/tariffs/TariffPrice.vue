<template>
    <div :class="$style.TariffPrice">
        <template v-if="getPrice">
            <span :class="$style.TextBig">{{ getPriceDiscount | splitThousands }} ₽</span>
            <span v-if="getDiscount" :class="$style.TextSmall">
                {{ getPrice | splitThousands }} ₽
            </span>
            <span v-if="getDiscount" :class="$style.Discount">{{ getDiscount }}</span>
        </template>
        <template v-else-if="showEmptyCheckboxesWarning">
            <span :class="$style.TextWarning">Активируйте хотя бы одну опцию</span>
        </template>
        <template v-else>
            <span :class="$style.TextBig">Бесплатно</span>
        </template>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        name: 'TariffPrice',
        data() {
            return {
                checkboxes: [],
            };
        },
        computed: {
            ...mapGetters({
                tariffsGrouped: 'tariffs/getTariffsGrouped',
                getSelectedSku: 'tariffs/getSelectedSku',
                getSelectedPeriod: 'tariffs/getSelectedPeriod',
                getSelectedPeriodDiscount: 'tariffs/getSelectedPeriodDiscount',
                isTariffPromo: 'tariffs/isTariffPromo',
                getCheckboxsesState: 'tariffs/getCheckboxsesState',
                itemsPerSku: 'tariffs/getItemsPerSku',
            }),
            checkboxesStateTrigger() {
                return JSON.stringify(
                    this.itemsPerSku.map(item => ({
                        checked: item.checked,
                    }))
                );
            },
            getPrice() {
                if (this.isTariffPromo) {
                    return 990;
                } else if (this.tariffsGrouped.length > 1) {
                    let sum = 0;
                    this.itemsPerSku.forEach((current, i) => {
                        if (current.price > 0 && this.checkboxes[i]) {
                            sum += Number(current.price);
                        }
                    });
                    return sum * this.getSelectedPeriod;
                } else {
                    return '';
                }

                // return 1;
            },
            getDiscount() {
                return this.getSelectedPeriodDiscount
                    ? `-${this.getSelectedPeriodDiscount}%`
                    : this.getSelectedPeriodDiscount;
            },
            getPriceDiscount() {
                const multiplier = 1 - this.getSelectedPeriodDiscount / 100;
                return this.getPrice * multiplier;
            },
            showEmptyCheckboxesWarning() {
                return Boolean(!this.checkboxes.filter(el => el === true).length);
            },
        },
        watch: {
            checkboxesStateTrigger: {
                immediate: true,
                handler(val) {
                    if (this.getCheckboxsesState.length) {
                        this.checkboxes = this.getCheckboxsesState;
                    }
                },
            },
        },
    };
</script>

<style lang="scss" module>
    .TariffPrice {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: size(4);
        height: size(48);
    }

    .TextBig {
        font-size: size(24);
        line-height: 1.375;
        font-weight: 500;
        color: $color-main-font;
    }

    .TextWarning {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        border-radius: size(8);
        background-color: rgba(255, 57, 129, 0.08);
        font-size: size(20);
        line-height: 1.375;
        font-weight: 500;
        color: $error;
    }

    .TextSmall {
        text-decoration: line-through;
        font-size: size(16);
        line-height: 1.375;
        font-weight: 500;
        color: $color-gray-light-100;
    }

    .Discount {
        margin-left: size(4);
        padding: size(4) size(8);
        border-radius: size(16);
        background-color: $error;
        font-size: size(12);
        line-height: 1.4;
        color: $white;
    }
</style>
