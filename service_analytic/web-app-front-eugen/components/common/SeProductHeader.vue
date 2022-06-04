<template>
    <div class="se-product-header">
        <div class="se-product-header__img-wrapper" @mouseover="imgOver" @mouseleave="imgLeave">
            <img class="se-product-header__img" :src="img" />
        </div>
        <VMenu
            v-model="modalState"
            :position-x="modalCoords.left"
            :position-y="modalCoords.top"
            absolute
            offset-y
        >
            <VCard class="se-product-header-vmenu-card">
                <img :src="img" alt="Изображение товара" />
            </VCard>
        </VMenu>
        <div class="se-product-header__title" :title="title">
            {{ titleSmall }}
        </div>
        <a :href="href" target="_blank" class="se-product-header__article">
            {{ id }}
            <SvgIcon class="se-product-header__article-img" name="outlined/link" data-right />
        </a>
        <template v-if="colorsForSelect.length > 1">
            <VSelect
                v-model="selectedColor"
                :items="colorsForSelect"
                background-color="#fff"
                label="Цвета"
                item-text="name"
                item-value="id"
                class="se-product-header__select light-outline"
                outlined
                dense
                hide-details
                :menu-props="{ nudgeBottom: 42 }"
                @change="selectOption"
            />
            <div class="se-product-header__label">
                {{ colorNumbStr }}
            </div>
        </template>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import { getCoords, getDeclWord } from '~/assets/js/utils/helpers';

    export default {
        props: {
            options: {
                type: Array,
                default: () => [],
            },
            title: {
                type: String,
                default: '',
            },
            url: {
                type: String,
                default: '',
            },
            product: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                selectedColor: 1,
                optionsIndex: 0,
                modalState: false,
                modalCoords: {},
            };
        },
        computed: {
            ...mapGetters(['isSelMpIndex', 'isSelectedMp']),
            ...mapGetters('product', ['getProduct']),

            selectedOption() {
                return this.options[this.optionsIndex];
            },
            colorsForSelect() {
                const colorsArr = [];
                try {
                    this.options.forEach(el => {
                        colorsArr.push({
                            id: el.nmId,
                            name:
                                String(el.name).charAt(0).toUpperCase() + String(el.name).slice(1),
                        });
                    });
                    return colorsArr;
                } catch {
                    return colorsArr;
                }
            },
            img() {
                return [this.getProduct.photo_url, this.selectedOption?.images[0]][
                    this.isSelMpIndex
                ];
            },
            id() {
                return this.isSelectedMp.id === 1
                    ? this.product.data.sku.fbo
                    : this.selectedOption.nmId;
            },
            titleSmall() {
                return this.title.length <= 70 ? this.title : this.title.slice(0, 70) + '...';
            },
            href() {
                const productId =
                    this.isSelectedMp.id === 1 ? this.product.data.sku.fbo : this.product.nmid;
                return this.url.replace(productId, this.id);
            },
            colorNumbStr() {
                const colorsNumb = this.options.length - 1;
                return (
                    '+ ' + colorsNumb + ' ' + getDeclWord(['цвет', 'цвета', 'цветов'], colorsNumb)
                );
            },
        },
        mounted() {
            this.selectedColor = this.selectedOption?.nmId;
        },
        methods: {
            selectOption(optionId) {
                this.options.forEach((el, i) => {
                    if (el.nmId == optionId) {
                        this.optionsIndex = i;
                    }
                });

                this.$store.commit('product/setActiveOptionIndex', this.optionsIndex);
            },
            imgOver() {
                const currentElem = event.target.classList.contains(
                    'se-product-header__img-wrapper'
                )
                    ? event.target
                    : event.target.closest('.se-product-header__img-wrapper');
                const { top, left } = getCoords(currentElem, true);
                const paddingLeft = 20;
                this.modalCoords = {
                    top,
                    left: currentElem.offsetWidth + left + paddingLeft,
                };
                this.modalState = true;
            },
            imgLeave() {
                this.modalState = false;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .se-product-header {
        position: relative;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        gap: 16px;
        height: 85px;
        margin-bottom: 16px;
        padding: 16px;
        border-radius: 16px;
        background-color: #fff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);

        &__img-wrapper {
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            min-width: 58px;
            height: 51px;
            padding: 2px 1px;
            border-radius: 8px;
            border: 1px solid #c8cfd9;
            background-color: #fff;
        }

        &__img {
            height: 100%;
        }

        &__title {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            font-size: 20px;
            font-weight: 700;
            word-break: break-word;
        }

        &__article {
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 400;
            line-height: 24px;
            color: #2f3640;

            &-img {
                width: 18px;
                height: 18px;
            }

            &:hover {
                color: #710bff;
            }
        }

        &__select {
            max-width: 280px;
        }

        &__label {
            box-sizing: border-box;
            height: 32px;
            padding: 8px 12px;
            border-radius: 8px;
            background-color: #e9edf2;
            white-space: nowrap;
            font-size: 12px;
            font-weight: 700;
            line-height: 16px;
            color: #7e8793;
        }
    }
</style>

<style lang="scss">
    .se-product-header-vmenu-card {
        width: 250px;

        & img {
            width: 100%;
        }
    }
</style>
