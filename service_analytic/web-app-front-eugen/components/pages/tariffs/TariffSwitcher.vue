<template>
    <div :class="$style.TariffSwitcher">
        <div v-ripple :class="$style.Arrow" @click="slideMinus">
            <SvgIcon name="outlined/chevronBack" />
        </div>
        <v-carousel
            v-model="activeOption"
            hide-delimiters
            :show-arrows="false"
            height="116"
            @change="handleSlideChange($event)"
        >
            <v-carousel-item v-for="item in tariffsGrouped" :key="item.id">
                <div :class="[$style.Description, { [$style.ComingSoon]: showComingSoon }]">
                    <div v-if="item.sku === getActivatedSku" :class="$style.TariffYours">
                        Ваш тариф
                    </div>
                    <div :class="$style.TariffType">
                        {{ item.sku === 3 ? 'Бесплатный' : 'Премиум' }}
                        <span v-if="showComingSoon" :class="$style.TariffTypeSoon">скоро</span>
                        <span v-if="item.promo" :class="$style.TariffTypePromo">Промо</span>
                    </div>
                    <div :class="$style.TariffSku" v-html="getTariffSkuText(item.sku)" />
                </div>
            </v-carousel-item>
        </v-carousel>
        <div v-ripple :class="$style.Arrow" @click="slidePlus">
            <SvgIcon name="outlined/chevronNext" />
        </div>
    </div>
</template>

<script>
    import { mapActions, mapGetters } from 'vuex';

    export default {
        name: 'TariffSwitcher',
        data() {
            return {
                model: 0,
                // activeOption: 0,
            };
        },
        computed: {
            ...mapGetters({
                isPromocodeEnteredAndValid: 'tariffs/isPromocodeEnteredAndValid',
                getSelectedSku: 'tariffs/getSelectedSku',
                getActivatedSku: 'tariffs/getActivatedSku',
                tariffsGrouped: 'tariffs/getTariffsGrouped',
            }),
            optionsKeys() {
                return this.tariffsGrouped.map(x => x.id);
            },
            activeOption: {
                get() {
                    return this.optionsKeys
                        ? this.optionsKeys.findIndex(el => el === this.getSelectedSku)
                        : 0;
                },
                set(val) {
                    this.setSelectedSku(this.optionsKeys[val]);
                },
            },
            showComingSoon() {
                // return !(this.getSelectedSku === 'promo' || this.getSelectedSku === '3');
                return false;
            },
        },
        watch: {
            model() {
                this.handleSlideChange();
            },
        },
        methods: {
            ...mapActions({
                setActiveOptionIndex: 'product/setActiveOptionIndex',
                setSelectedSku: 'tariffs/setSelectedSku',
            }),
            slideMinus() {
                if (this.activeOption <= 0) {
                    this.setSelectedSku(this.optionsKeys[this.optionsKeys.length - 1]);
                } else {
                    this.setSelectedSku(this.optionsKeys[this.activeOption - 1]);
                }
            },
            slidePlus() {
                if (this.activeOption >= this.optionsKeys.length - 1) {
                    this.setSelectedSku(this.optionsKeys[0]);
                    // this.model = 0;
                } else {
                    this.setSelectedSku(this.optionsKeys[this.activeOption + 1]);
                }
            },
            handleSlideChange() {
                this.$emit('optionChange', this.model);
            },
            getTariffSkuText(sku) {
                if (sku === 3) {
                    return `${sku} SKU`;
                } else {
                    return `До ${sku} SKU`;
                }
            },
        },
    };
</script>

<style lang="scss" module>
    .TariffSwitcher {
        display: grid;
        grid-template-columns: size(60) auto size(60);
        grid-template-rows: none;
        width: 100%;
        border-radius: 0.5rem;
        border: 1px $color-gray-blue-light solid;

        &.WrapperSingle {
            grid-template-columns: auto;
            overflow: hidden;
        }
    }

    .Arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .Description {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        //margin: auto;
        padding: size(16);
        //background-color: $color-main-background;

        &.ComingSoon {
            color: $base-600;
        }
    }

    .TariffYours {
        font-size: size(12);
        line-height: 2;
        font-weight: bold;
        color: $color-purple-primary;
    }

    .TariffType {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: size(6);
        font-size: size(16);
        line-height: 1.375;
        font-weight: 500;
    }

    .TariffTypePromo {
        padding: size(4) size(8);
        border-radius: size(16);
        background-color: $color-purple-primary;
        font-size: size(12);
        line-height: 1.33;
        font-weight: bold;
        color: $white;
    }

    .TariffTypeSoon {
        padding: size(4) size(8);
        border-radius: size(16);
        background-color: $base-400;
        font-size: size(12);
        line-height: 1.33;
        font-weight: bold;
        color: $base-600;
    }

    .TariffSku {
        font-size: size(28);
        line-height: 1.36;
        font-weight: 500;
    }
</style>
