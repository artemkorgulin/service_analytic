<template>
    <BaseDrawer
        v-model="isShow"
        :class="$style.ModalSearchOptimization"
        content-class="modal-confirm-content"
        width="599px"
    >
        <!-- <VCard :class="$style.wrapper"> -->
        <VFadeTransition mode="out-in" appear>
            <div key="content" :class="$style.inner" class="custom-scrollbar">
                <h4 :class="$style.heading">Подбор ключевых запросов</h4>

                <div :class="$style.inputsWrapper">
                    <VAutocomplete
                        v-model="typesInput"
                        :items="typesList"
                        label="Выберите до 3 предметов"
                        counter="3"
                        outlined
                        multiple
                        @change="handleTypesInputChange"
                    ></VAutocomplete>
                </div>

                <div :class="$style.textWrapper">
                    <div :class="$style.text">
                        Вы можете удалить лишнее ключевое слово, чтобы добавить на его место новое.
                        Также можете обновить все слова
                    </div>
                    <VBtn
                        :class="$style.btn"
                        outlined
                        :disabled="!filterArray.length"
                        :loading="isUpdateLoading"
                        @click="handlePickListUpdate"
                    >
                        Обновить
                    </VBtn>
                </div>

                <div :class="$style.table">
                    <div :class="$style.tableHeader">
                        <div :class="$style.tableHeaderTitle">Название товара</div>
                        <div :class="$style.tableHeaderPopular">Популярность</div>
                    </div>
                    <div :class="$style.tableBody">
                        <div
                            v-for="keyword in getPickListModal"
                            :key="keyword.id"
                            :class="$style.tableBodyItem"
                        >
                            <div :class="$style.tableBodyItemLeft">
                                <VBtn
                                    :class="$style.tableBodyItemDeleteBtn"
                                    icon
                                    @click="handleKeywordDelete(keyword)"
                                >
                                    <SvgIcon
                                        :class="$style.tableBodyItemIcon"
                                        name="outlined/deleteTrash"
                                    />
                                </VBtn>
                                <div :class="$style.textNormal">{{ keyword.name }}</div>
                            </div>
                            <div :class="$style.tableBodyItemRight">
                                <div :class="$style.textNormal">{{ keyword.popularity }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :class="$style.btnWrapper">
                    <VBtn
                        :class="$style.btn"
                        color="accent"
                        large
                        block
                        :disabled="!getPickListModal.length"
                        @click="handleApply"
                    >
                        Применить
                    </VBtn>
                </div>
            </div>
        </VFadeTransition>
        <!-- </VCard> -->
    </BaseDrawer>
</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex';
    import { defineComponent } from '@nuxtjs/composition-api';
    import { errorHandler } from '~utils/response.utils';

    export default defineComponent({
        name: 'ModalSearchOptimization',
        data() {
            return {
                isShow: true,
                isLoading: false,
                isUpdateLoading: false,
                typesInput: [],
                typesList: [],
            };
        },
        async fetch() {
            this.typesList = await this.fetchTypesList();
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                productId: 'product/getProductId',
                pickListSorted: 'product/getPickListSorted',
                getPickListModal: 'product/getPickListModal',
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                productWildberries: 'product/getProductWildberries',
                productOzon: 'product/GET_PRODUCT',
            }),
            pickListSortedFlat() {
                return this.pickListSorted.flat();
            },
            filterArray() {
                return [this.getBrand, this.getCategory, ...this.typesInput];
            },
            getBrand() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.brand || '';

                    default:
                        return (
                            Object.values(this.productOzon.data.characteristics).find(
                                el => el.name === 'Бренд'
                            ).selected_options[0].value || ''
                        );
                }
            },
            getCategory() {
                switch (this.marketplaceSlug) {
                    case 'wildberries':
                        return this.productWildberries.category.name || '';

                    default:
                        return this.productOzon.data.web_category_name || '';
                }
            },
        },
        methods: {
            ...mapActions({
                fetchPickListWidlberries: 'product/fetchPickListWidlberries',
                setPickListSorted: 'product/setPickListSorted',
                saveOptimizationWildberries: 'product/saveOptimizationWildberries',
                handleKeywordDelete: 'product/handleKeywordDeleteModal',
                fetchKeywordByFilters: 'product/fetchKeywordByFilters',
                addPickListFromModal: 'product/addPickListFromModal',
            }),
            ...mapMutations({
                setPickListModal: 'product/SET_PICK_LIST_MODAL',
            }),
            async handleConfirm() {
                this.isLoading = true;
                try {
                    this.isLoading = false;
                    this.isShow = false;
                } catch (error) {
                    this.isLoading = false;
                    this.isShow = false;
                    return this?.$sentry?.captureException(error);
                }
            },
            handleClose() {
                this.typesInput = [];
                this.typesList = [];
                this.setPickListModal([]);
                return this.$modal.close();
            },
            handleApply() {
                this.addPickListFromModal();
                return this.handleClose();
            },
            async handlePickListUpdate() {
                this.isUpdateLoading = true;

                try {
                    const { productId: id, filterArray, fetchKeywordByFilters } = this;

                    const data = {
                        id,
                        filterArray,
                    };

                    await fetchKeywordByFilters(data);
                } catch (error) {
                    console.error(error);
                }

                this.isUpdateLoading = false;
            },
            async fetchTypesList() {
                const productId = await this.productId;
                const url =
                    this.marketplaceSlug === 'wildberries'
                        ? `/api/an/v1/wb/subjects?product_id=${productId}`
                        : `/api/vp/v1/user_categories?product_id=${productId}`;

                return new Promise((resolve, reject) => {
                    this.$axios.$get(url).then(({ data }) => {
                        console.log('fetchTypesList ', data);
                        resolve(Object.values(data.all_subjects) || []);
                    });
                }).catch(({ response }) => {
                    errorHandler(response, this.$notify);
                });
            },
            handleTypesInputChange() {
                if (this.typesInput.length > 3) {
                    this.typesInput.shift();
                }
                this.typesList.sort(
                    (a, b) => this.typesInput.indexOf(b) - this.typesInput.indexOf(a)
                );
            },
        },
    });
</script>

<style lang="scss" module>
    .ModalSearchOptimization {
        height: 100%;
        max-height: 100%;
    }

    .wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }

    .inputsWrapper {
        display: flex;
        gap: 16px;
        flex-direction: column;
    }

    .textWrapper {
        display: flex;

        .text {
            padding: 0 5px;
            font-size: 14px;
        }
    }

    .table {
        flex-grow: 1;
        flex-shrink: 1;
        border-radius: 8px;
        border: 1px solid #e9edf2;
    }

    .tableHeader {
        display: flex;
        height: 56px;
    }

    .tableHeaderTitle {
        flex: 0 1 65%;
        border-right: 1px solid #e9edf2;
    }

    .tableHeaderPopular {
        flex: 0 1 35%;
    }

    .tableHeaderTitle,
    .tableHeaderPopular {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e9edf2;
    }

    .tableBodyItem {
        display: flex;
        align-items: center;
        gap: size(8);

        & .tableBodyItemLeft {
            display: flex;
            align-items: center;
            flex: 0 1 65%;
            gap: size(8);
        }

        & .tableBodyItemRight {
            display: flex;
            align-items: center;
            flex: 0 1 35%;
            padding-left: 20px;
        }

        & .tableBodyItemDeleteBtn {
            border-color: transparent;
            background-color: transparent;
        }

        & .tableBodyItemIcon {
            width: size(16);
        }

        & .textNormal {
            font-size: size(14);
            font-weight: 500;
            line-height: 1.4;
        }

        .tableBody {
            flex-grow: 1;
            flex-shrink: 1;
            padding: 20px;
        }
    }

    .inner {
        display: flex;
        gap: 24px;
        width: 100%;
        max-width: 100%;
        height: 100%;
        max-height: 100%;
        padding: 16px;
        flex-direction: column;
    }

    .heading {
        @extend %text-h4;

        text-align: left;
    }

    .loadingWrapper {
        @include centerer;
    }

    .btnWrapper {
        margin-top: auto;
        padding-top: size(16);
    }

    .staticData {
        display: flex;
        gap: 24px;
        max-width: 100%;

        .staticDataItem {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            .staticDataType {
                @include date-subtitle;
            }

            .staticDataValue {
                color: $black;
            }
        }
    }
</style>
