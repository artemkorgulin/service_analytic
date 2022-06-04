<template>
    <VForm ref="formAction" class="custom-form">
        <VContainer>
            <VRow>
                <VCol>
                    <div class="new-product-media__label-head">
                        <div class="new-product-media__label-title">
                            <span class="title-h4 title-h4--medium title-h4--black">Фото</span>
                            <div class="new-product-media__label-count">
                                {{ strToArray(form.fields.images).length }} / 30
                            </div>
                        </div>

                        <span class="base-txt">
                            Ссылки на фото нужно разделить пробелом или клавишей Enter. Товар должен
                            полностью помещаться на фото. Фотографии должны быть хорошего качества.
                        </span>
                    </div>
                    <VTextarea
                        v-model="form.fields.images"
                        :rules="form.rules.images"
                        rows="8"
                        outlined
                        dense
                        no-resize
                        color="#710bff"
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <div class="new-product-media__label-head">
                        <div class="new-product-media__label-title">
                            <span class="title-h4 title-h4--medium title-h4--black">Фото 360</span>
                            <div class="new-product-media__label-count">
                                {{ strToArray(form.fields.images3d).length }} / 15
                            </div>
                        </div>

                        <span class="base-txt">
                            Через запятую добавьте ссылки на фото так, чтобы ракурс каждого
                            следующего фото отличался от предыдущего на равный угол. Нужно как
                            минимум 15 фотографий.
                        </span>
                    </div>
                    <VTextarea
                        v-model="form.fields.images3d"
                        :rules="form.rules.images3d"
                        rows="8"
                        outlined
                        dense
                        no-resize
                        color="#710bff"
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol>
                    <div class="new-product-media__label-head">
                        <div class="new-product-media__label-title">
                            <span class="title-h4 title-h4--medium title-h4--black">Видео</span>
                        </div>

                        <span class="base-txt">Вставьте ссылку на видео YouTube.</span>
                    </div>
                    <VTextField
                        v-model="form.fields.youtubecodes"
                        :rules="form.rules.youtubecodes"
                        outlined
                        dense
                        color="#710bff"
                    />
                </VCol>
            </VRow>
        </VContainer>
    </VForm>
</template>

<script>
    import formMixin from '~mixins/form.mixin';

    const strToArray = value =>
        value
            .replace(/ /g, '\n')
            .split('\n')
            .filter(link => Boolean(link))
            .map(link => link.trim());

    export default {
        name: 'StepThreeOzon',
        mixins: [formMixin],
        data() {
            return {
                radio: 0,
                form: {
                    fields: {
                        images: '',
                        images3d: '',
                        youtubecodes: '',
                    },
                    rules: {
                        images: [val => Boolean(val) || 'пожалуйста, укажите хотя бы одну ссылку'],
                        youtubecodes: [
                            val => typeof val === 'string' || 'поле заполнено не корректно',
                        ],
                    },
                },
            };
        },
        watch: {
            invalid(val) {
                this.$emit('setValid', !val);
            },
        },
        methods: {
            strToArray(val) {
                return strToArray(val);
            },
            getFields() {
                return this.getInputs;
            },
        },
    };
</script>

<style lang="scss">
    .new-product-media {
        &__label {
            &-head {
                margin-bottom: 16px;
                text-align: left;
            }

            &-title {
                display: flex;
                max-height: max-content;
                margin-bottom: 16px;
            }

            &-count {
                margin-left: 8px;
                font-size: 16px;
                color: #7e8793;
                font-weight: 600;
            }
        }
    }
</style>
