<template>
    <BaseDialog v-model="open" width="560">
        <VCard :class="$style.wrapper" class="custom-dialog dialog-questions">
            <div class="custom-dialog__header custom-dialog__header--close">
                <span class="custom-dialog__main-title">{{ content.title }}</span>
            </div>

            <div class="custom-dialog__body">
                <span class="custom-dialog__text txt-center" v-html="content.subtitle"></span>

                <VForm
                    ref="formAction"
                    :key="key"
                    class="custom-form custom-form--not-required-symbol"
                >
                    <VTextarea
                        v-model="form.fields.value"
                        :rules="form.rules.value"
                        label="Ссылка на фото"
                        outlined
                        dense
                        rows="8"
                        no-resize
                        color="#710bff"
                    />
                </VForm>
            </div>

            <div class="custom-dialog__footer">
                <VBtn class="full-width-wrap" color="accent" @click="save">Сохранить</VBtn>
            </div>
        </VCard>
    </BaseDialog>
</template>

<script>
    import { mapGetters } from 'vuex';
    import formMixin from '~mixins/form.mixin';

    const validURL = val => {
        const regexp =
            /^(ftp|http|https|chrome|:\/\/|\.|@){2,}(localhost|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|\S*:\w*@)*([a-zA-Z]|(\d{1,3}|\.){7}){1,}(\w|\.{2,}|\.[a-zA-Z]{2,3}|\/|\?|&|:\d|@|=|\/|\(.*\)|#|-|%)*$/gmu;
        return regexp.test(val);
    };

    export default {
        name: 'UploadDialog',
        mixins: [formMixin],
        props: {
            rules: {
                require: true,
                type: Object,
                default: null,
            },
        },
        data() {
            return {
                open: false,
                key: null,
                oldValue: null,
                form: {
                    fields: {
                        value: null,
                    },
                    rules: {},
                },
            };
        },
        computed: {
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            content() {
                switch (this.key) {
                    case 'images':
                        return {
                            title: 'Фото',
                            subtitle:
                                'Ссылки на фото нужно разделить пробелом или клавишей Enter.<br>Товар должен полностью помещаться на фото.<br>Фотографии должны быть хорошего качества.',
                        };
                    case 'images360':
                        return {
                            title: 'Фото 360',
                            subtitle:
                                'Через запятую добавьте ссылки на фото так,<br>чтобы ракурс каждого следующего фото<br>отличался от предыдущего на равный угол.<br>Укажите от ' +
                                this.rules[this.marketplaceSlug].images360.min +
                                ' до ' +
                                this.rules[this.marketplaceSlug].images360.max +
                                ' фотографий.',
                        };
                    default:
                        return {};
                }
            },
        },
        methods: {
            show(key, value) {
                const getValue = value.join('\n');

                this.open = true;
                this.key = key;

                this.oldValue = getValue;
                this.form.fields.value = getValue;

                this.setRules();
            },
            setRules() {
                const rules = this.rules[this.marketplaceSlug];
                const min = this.key === 'images' ? rules.images.min : rules.images360.min;
                const max = this.key === 'images' ? rules.images.max : rules.images360.max;
                const rulesValid = [
                    val => {
                        if (this.key !== 'images' && !val) {
                            return true;
                        }

                        let checkLink = false;
                        const links = val
                            .replace(/ /g, '\n')
                            .split('\n')
                            .filter(link => Boolean(link))
                            .map(link => link.trim());

                        const checkUniqLinks = links.filter(
                            (sku, index) => links.indexOf(sku) !== index
                        );

                        if (checkUniqLinks.length) {
                            return `Найдены повторы: ${checkUniqLinks.join(', ')}`;
                        } else {
                            for (let i = 0; i < links.length; i++) {
                                const check = validURL(links[i]);

                                if (!check) {
                                    checkLink = `Строка "${links[i]}" не является ссылкой`;
                                    break;
                                }
                            }

                            if (checkLink) {
                                return checkLink;
                            } else if (links.length > max) {
                                return `Вы можете указать максимум ${max} шт.`;
                            } else if (links.length < min) {
                                return `Вы должны указать минимум ${min} шт.`;
                            }
                        }

                        return true;
                    },
                ];

                if (this.key === 'images') {
                    rulesValid.push(val => Boolean(val) || 'Пожалуйста, заполните это поле');
                }

                this.form.rules.value = rulesValid;
            },
            save() {
                this.getInputs().then(result => {
                    if (result) {
                        this.$emit('saveChange', this.key, this.getImagesArray(result));
                        this.open = false;
                    }
                });
            },
            getImagesArray(result) {
                return result.value.length ? result.value.trim().split('\n') : [];
            },
        },
    };
</script>

<style lang="scss" module>
    .wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        border-radius: inherit;
    }
</style>
