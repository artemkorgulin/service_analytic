<template>
    <BaseDialog v-model="show" content-class="modal-create-new-product" width="860">
        <VCard class="custom-dialog">
            <div class="custom-dialog__header custom-dialog__header--close">
                <span class="custom-dialog__main-title">Создание нового товара</span>
            </div>

            <div class="custom-dialog__body">
                <VStepper alt-labels :value="steps" :class="$style.stepper" tile flat>
                    <VStepperHeader>
                        <VStepperStep :step="1">Основные данные</VStepperStep>

                        <VDivider />

                        <VStepperStep :step="2">Характеристики</VStepperStep>

                        <VDivider />

                        <VStepperStep :step="3">Медиа</VStepperStep>
                    </VStepperHeader>
                </VStepper>

                <div class="custom-scrollbar">
                    <keep-alive>
                        <component
                            :is="getComponent"
                            :ref="getComponent"
                            :key="getComponent + '-' + key"
                            @setValid="setValid"
                        />
                    </keep-alive>
                </div>
            </div>

            <div class="custom-dialog__footer">
                <VRow>
                    <VCol>
                        <VBtn
                            class="default-btn default-btn--full-width"
                            outlined
                            @click="stepBack"
                        >
                            {{ steps ? 'Назад' : 'Отмена' }}
                        </VBtn>
                    </VCol>
                    <VCol>
                        <VBtn
                            class="primary-btn--full-width"
                            color="accent"
                            :loading="pending"
                            @click="stepNext"
                        >
                            {{ 2 > steps ? 'Далее' : 'Создать' }}
                        </VBtn>
                    </VCol>
                </VRow>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { mapGetters } from 'vuex';
    import { errorHandler } from '~utils/response.utils';
    import StepOneOzon from '~/components/pages/products/Modals/CreationNewProduct/Ozon/StepOneOzon.vue';
    import StepTwoOzon from '~/components/pages/products/Modals/CreationNewProduct/Ozon/StepTwoOzon.vue';
    import StepThreeOzon from '~/components/pages/products/Modals/CreationNewProduct/Ozon/StepThreeOzon.vue';
    import StepOneWB from '~/components/pages/products/Modals/CreationNewProduct/WB/StepOneWB.vue';
    import StepTwoWB from '~/components/pages/products/Modals/CreationNewProduct/WB/StepTwoWB.vue';
    import StepThreeWB from '~/components/pages/products/Modals/CreationNewProduct/WB/StepThreeWB.vue';

    export default {
        name: 'CreationNewProduct',
        components: {
            StepOneOzon,
            StepTwoOzon,
            StepThreeOzon,
            StepOneWB,
            StepTwoWB,
            StepThreeWB,
        },
        data() {
            return {
                key: 0,
                steps: 0,
                show: true,
                isValid: false,
                pending: false,
                data: {
                    StepOne: null,
                    StepTwo: null,
                    StepThree: null,
                },
            };
        },
        computed: {
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            getComponent() {
                switch (this.steps - 1) {
                    case 1:
                        return this.marketplaceSlug === 'wildberries' ? 'StepTwoWB' : 'StepTwoOzon';
                    case 2:
                        return this.marketplaceSlug === 'wildberries'
                            ? 'StepThreeWB'
                            : 'StepThreeOzon';
                    default:
                        return this.marketplaceSlug === 'wildberries' ? 'StepOneWB' : 'StepOneOzon';
                }
            },
        },
        watch: {
            steps() {
                this.$nextTick(() => {
                    const check = this.$refs[this.getComponent].invalid;
                    this.isValid = !check;
                });
            },
        },
        methods: {
            stepBack() {
                if (this.steps) {
                    this.steps -= 1;
                } else {
                    this.show = false;
                }
            },
            async stepNext() {
                const check = await this.$refs[this.getComponent].getInputs();

                if (check) {
                    this.data[this.getComponent] = check;

                    if (this.steps !== 2) {
                        this.steps += 1;
                    } else {
                        const characteristics = this.data.StepTwo;
                        const body = {
                            ...this.data.StepOne,
                            ...this.data.StepThree,
                            characteristics: Object.keys(characteristics).reduce((acc, current) => {
                                const item = characteristics[current];
                                acc[current] = {
                                    id: current,
                                    value: item,
                                };
                                return acc;
                            }, {}),
                        };

                        body.images = body.images.replace(/ /g, '\n').split('\n');
                        body.images3d = body.images3d.replace(/ /g, '\n').split('\n');

                        this.pending = true;

                        this.$axios
                            .$post('/api/vp/v2/create-product/' + this.data.StepOne.category_id, {
                                ...body,
                            })
                            .then(() => {
                                this.key += 1;
                                this.show = false;
                                this.steps = 0;

                                this.$store.dispatch('products/LOAD_PRODUCTS', {
                                    type: 'common',
                                    reload: true,
                                });
                            })
                            .catch(({ response }) => {
                                errorHandler(response, this.$notify);
                            })
                            .finally(() => {
                                this.pending = false;
                            });
                    }
                }
            },
            setValid(data) {
                this.isValid = data;
            },
        },
    };
</script>
<style lang="scss">
    // .custom-scrollbar {
    //     max-height: 600px;
    //     margin-bottom: 16px;
    // }
</style>
<style lang="scss" module>
    .stepper {
        :global(.v-stepper__label) {
            text-align: center;
        }
    }

    :global(.modal-create-new-product) {
        overflow: hidden;
    }
</style>
