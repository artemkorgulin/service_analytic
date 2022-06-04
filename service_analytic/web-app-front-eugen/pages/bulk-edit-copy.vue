<template>
    <div ref="pageContainer" v-resize="resetTmpl" class="bulk-edit-copy-page">
        <div class="page">
            <div class="page__header d-flex align-center flex-wrap" style="gap: 16px">
                <VBtn outlined color="#7e8793" class="pr-4 pl-2" @click="$router.go(-1)">
                    <SvgIcon name="outlined/arrowLeft" style="height: 16px" />
                    Назад
                </VBtn>
                <h2 class="page__title">Массовое редактирование</h2>
                <div class="se-select-mp-shell">
                    <se-select-mp
                        :with-confirm-window="step !== 1"
                        @resetAccount="
                            selectedTypeId = undefined;
                            step = 1;
                        "
                    />
                </div>
            </div>
            <div class="page__content">
                <SeStepItem
                    :number="1"
                    title="Выберите категорию, которую хотите редактировать или товар для копирования характеристик"
                ></SeStepItem>
                <div v-if="isSelectedMp.id === 1" class="page__gray-info mt-4">
                    В таблице находятся только товары, успешно модерированные на Ozon. Чтобы
                    копировать другой товар, заполните его карточку и отправьте на маркетплейс.
                    После прохождения модерации он будет доступен для копирования
                </div>
                <v-radio-group v-model="selectedTypeId" hide-details @change="changeStep()">
                    <div class="se-radio-lb d-flex mb-4">
                        <div
                            v-for="(item, index) in selectionType"
                            :key="item"
                            class="se-radio-lb__item"
                        >
                            <v-radio dense :value="index" :label="item"></v-radio>
                        </div>
                    </div>
                </v-radio-group>
                <component
                    :is="selectionTypeComp[selectedTypeId]"
                    v-if="step === 1"
                    @setCategory="nextStep"
                />
                <div v-if="step !== 1" class="bulk-edit-copy-page__step">
                    <div class="bulk-edit-copy-page__selected-category mb-4">
                        Выбранная категория:
                        {{
                            typeof selectedCategory === 'object'
                                ? selectedCategory.name
                                : selectedCategory
                        }}
                    </div>
                    <SeStepItem
                        :number="2"
                        :title="
                            [
                                'Отфильтруйте товары  и протяните характеристики по столбцу, как в Excel',
                                'Выберите характеристики',
                            ][Number(mode === 'copy')]
                        "
                    ></SeStepItem>
                    <ProductsTableAg
                        ref="tableForBulkEditing"
                        :selected-product-id="selectedProductId"
                        :mode="mode"
                        :category="
                            typeof selectedCategory === 'object'
                                ? selectedCategory.id
                                : selectedCategory
                        "
                        @finishSaveLoader="finishSave($event)"
                    />
                </div>
            </div>
        </div>
        <div v-show="step === 2" ref="prodActions" class="page__footer-actions footer-actions">
            <div class="footer-actions__content">
                <VBtn
                    v-if="mode === 'copy'"
                    depressed
                    outlined
                    class="se-btn mr-5"
                    @click="$refs.tableForBulkEditing.copyCharacteristic()"
                >
                    Посмотрeть изменения
                </VBtn>
                <VBtn
                    color="primary"
                    depressed
                    :disabled="delayEnabled"
                    :loading="saveLoading"
                    class="se-btn pl-12 pr-12"
                    @click="sendOnSave"
                >
                    {{ saveBtnText }}
                </VBtn>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import ProductsTableAg from '~/components/pages/bulkEditCopy/ProductsTableAg';
    import { getDeclWord } from '~/assets/js/utils/helpers';

    export default {
        components: { ProductsTableAg },
        data() {
            return {
                step: 1,
                selectedCategory: '',
                selectedProductId: '',
                selectedTypeId: undefined,
                selectionType: ['Товары', 'Категории'],
                selectionTypeComp: ['ProductList', 'ListOfCategories'],
                saveLoading: false,
                delayEnabled: false,
                btnDelay: 30,
                btnTimer: undefined,
            };
        },
        computed: {
            ...mapGetters(['isSelectedMp', 'isSelMpIndex']),
            ...mapState('user', ['isMenuExpanded']),
            mode() {
                return ['copy', 'edit'][this.selectedTypeId];
            },
            saveBtnText() {
                const { btnTimer, delayEnabled, isSelMpIndex } = this;
                const secondDecl = ['секунду', 'секунды', 'секунд'];
                return delayEnabled
                    ? `Подождите ${btnTimer} ${getDeclWord(secondDecl, btnTimer)} ...`
                    : `Отправить в ${['Ozon', 'WB'][isSelMpIndex]}`;
            },
        },
        watch: {
            isMenuExpanded() {
                setTimeout(this.resetTmpl, 200);
            },
        },
        created() {
            this.copyFromProduct();
        },
        methods: {
            changeStep() {
                if (this.step !== 1) {
                    this.step = 1;
                }
            },
            finishSave(successfully) {
                setTimeout(() => {
                    this.saveLoading = false;
                    if (successfully) {
                        this.enableDelayOnSaveBtn();
                    }
                }, 200);
            },
            enableDelayOnSaveBtn() {
                this.btnTimer = this.btnDelay;
                this.delayEnabled = true;
                const interval = setInterval(() => {
                    this.btnTimer -= 1;
                    if (this.btnTimer === 0) {
                        this.delayEnabled = false;
                        clearInterval(interval);
                    }
                }, 1000);
            },
            async copyFromProduct() {
                const { product_id, category } = this.$route.query;
                if (product_id && category) {
                    try {
                        this.selectedTypeId = 0;
                        this.selectedCategory =
                            this.isSelectedMp.id === 1
                                ? await this.getIdCategoryByName(category)
                                : category;
                        this.selectedProductId = product_id;
                        this.step = 2;
                    } catch (error) {
                        console.error(error);
                    }
                }
            },
            async getIdCategoryByName(category) {
                /* eslint-disable */
                const topic = '/api/vp/v2/ozon/account-categories';
                const params = {};
                params['query[search]'] = category;
                params.per_page = 25;
                params.page = 1;

                const {
                    data: {
                        data: { data },
                    },
                } = await this.$axios.get(topic, { params });

                const { id, name } = data[0];
                return { id, name };
            },
            sendOnSave() {
                const { tableForBulkEditing } = this.$refs;
                tableForBulkEditing.saveProducts();
                this.saveLoading = true;
            },
            nextStep(item) {
                const methods = {
                    copy: () => {
                        const category = item[['category', 'object'][this.isSelMpIndex]];
                        this.selectedCategory = category;
                        this.selectedProductId = item.id;
                    },
                    edit: () => {
                        this.selectedCategory = item;
                    },
                };

                if (this.mode in methods) {
                    methods[this.mode]();
                    this.step += 1;
                }
            },
            resetTmpl() {
                try {
                    const { prodActions, pageContainer } = this.$refs;
                    const actionContainer = prodActions.querySelector('.footer-actions__content');
                    const containerWidth = pageContainer.offsetWidth;

                    actionContainer.style.width = `${containerWidth}px`;
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .bulk-edit-copy-page {
        &__selected-category {
            font-size: 14px;
        }
    }

    .footer-actions {
        position: fixed;
        bottom: 0;
        z-index: 3;
        width: 100%;
        background: white;
        -webkit-box-shadow: 0 -4px 12px rgb(0 0 0 / 6%);
        box-shadow: 0 -4px 12px rgb(0 0 0 / 6%);

        &__content {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 20px 23px;
        }
    }

    .page {
        &__gray-info {
            padding: 12px;
            border-radius: $border-radius-sm;
            background: $base-100;
            font-size: 14px;
        }
    }

    .se-select-mp-shell {
        position: relative;

        &__accept-click {
            position: absolute;
            z-index: 10;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
    }
</style>
