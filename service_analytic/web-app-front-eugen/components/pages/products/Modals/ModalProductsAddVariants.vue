<template>
    <BaseDialog v-model="isShow" content-class="modal-products-add-variants">
        <VCard :class="$style.wrapper">
            <div class="dialog-way-add-product__box">
                <div class="dialog-way-add-product__block" @click="openAddNewProducts">
                    <img
                        class="dialog-way-add-product__icon"
                        src="/images/desing-icons.svg"
                        alt=""
                    />
                    <span class="dialog-way-add-product__title">
                        Добавить товары из Ozon (массов)
                    </span>
                </div>
                <div class="dialog-way-add-product__block" @click="openAddNewProduct">
                    <img
                        class="dialog-way-add-product__icon"
                        src="/images/desing-icons.svg"
                        alt=""
                    />
                    <span class="dialog-way-add-product__title">Добавить товар (один товар)</span>
                </div>
                <div class="dialog-way-add-product__block" @click="openCreationNewProduct">
                    <img
                        class="dialog-way-add-product__icon"
                        src="/images/desing-icons.svg"
                        alt=""
                    />
                    <span class="dialog-way-add-product__title">Создать товар</span>
                </div>
            </div>
            <!-- <div v-ripple
                 :class="$style.item"
                 class="dialog-way-add-product__block"
                 @click="handleOpenAddNewProduct"
            >
                <VImg :class="$style.img"
                      src="/images/products-add-ozon.svg"
                      contain
                      alt="Добавить товары из Ozon"
                />
                <div :class="$style.heading" >Добавить товары из Ozon</div>
            </div>
            <div v-ripple
                 :class="$style.item"
                 class="dialog-way-add-product__block"
                 @click="handleOpenCreationNewProduct"
            >
                <VImg :class="$style.img"
                      src="/images/products-add-create.svg"
                      contain
                      alt="Создать товар"
                />
                <div :class="$style.heading" >Создать товар</div>
            </div> -->
        </VCard>
    </BaseDialog>
</template>

<script>
    export default {
        name: 'ModalProductsAddVariants',
        data() {
            return {
                isShow: true,
                isLoading: false,
            };
        },
        methods: {
            async openAddNewProduct() {
                await this.$modal.close();
                await this.$modal.open({ component: 'AddNewProduct' });
            },
            async openAddNewProducts() {
                await this.$modal.close();
                await this.$modal.open({ component: 'AddNewProducts' });
            },
            async openCreationNewProduct() {
                await this.$modal.close();
                await this.$modal.open({ component: 'CreationNewProduct' });
            },
        },
    };
</script>
<style lang="scss" module>
    :global(.modal-products-add-variants) {
        max-width: 916px;
    }

    .wrapper {
        display: flex;
        justify-content: space-between;
        min-height: 300px;
        padding: 24px;
        border-radius: inherit;
        gap: 24px;

        @include respond-to(sm) {
            flex-direction: column;
        }
    }

    .item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        justify-content: center;
        flex: 1 1 auto;
        border-radius: 8px;
        border: 1px solid $color-gray-blue-light;
        transition: $transition-fast;
        cursor: pointer;
        flex-direction: column;
        user-select: none;

        &:active {
            border-color: $color-purple-primary;
            box-shadow: 0 4px 64px rgba(0, 0, 0, 0.16);
        }

        @include hover {
            &:hover {
                border-color: $color-purple-primary;
                box-shadow: 0 4px 64px rgba(0, 0, 0, 0.16);
            }
        }

        @include respond-to(sm) {
            padding: 20px;
        }

        @include respond-to(xs) {
            width: 100%;
            padding: 16px;

            &:first-child {
                margin-bottom: 24px;
            }
        }
    }

    .heading {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
    }

    .img {
        max-width: 100%;
        height: 180px;
        max-height: 180px;
        margin-bottom: 8px;
        pointer-events: none;

        @include respond-to(xs) {
            width: 160px;
        }
    }
</style>

<style lang="scss">
    .dialog-way-add-product {
        width: 100%;
        max-width: 916px;

        &__box {
            display: flex;
            justify-content: space-between;

            @include phone-small {
                flex-direction: column;
            }
        }

        &__block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: calc((100% - 2rem) / 3);
            padding: size(32) size(32) size(48);
            border-radius: 8px;
            border: 1px solid $color-gray-blue-light;
            transition: $transition-fast;
            cursor: pointer;
            flex-direction: column;

            &:active {
                border-color: $color-purple-primary;
                box-shadow: 0 12px 24px rgba(95, 69, 255, 0.32);
            }

            @include hover {
                &:hover {
                    border-color: $color-purple-primary;
                    box-shadow: 0 12px 24px rgba(95, 69, 255, 0.32);
                }
            }

            @include phone-large {
                padding: 20px;
            }

            @include phone-small {
                width: 100%;
                padding: 16px;

                &:first-child {
                    margin-bottom: size(24);
                }
            }
        }

        &__title {
            font-size: size(22);
            font-weight: bold;
        }

        &__icon {
            width: 248px;
            max-width: 100%;
            margin-bottom: 8px;
            pointer-events: none;

            @include phone-small {
                width: 160px;
            }
        }
    }
</style>
