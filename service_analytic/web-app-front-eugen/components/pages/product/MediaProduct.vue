<template>
    <div class="pt-0 pr-2 pl-2">
        <template v-if="showRecom">
            <SeAlert v-if="showError" type="alert" class="mb-6">
                Минимум у конкурентов с первой страницы в категории - {{ infoTop36.photo }} фото. Загрузите еще
                {{ infoTop36.photo - totalImg }}
                {{ getDeclrImg(infoTop36.photo - totalImg) }}, чтобы чтобы конкурировать в категории
            </SeAlert>
            <SeAlert v-else type="success" class="mb-6">
                Минимум у конкурентов с первой страницы в категории - {{ infoTop36.photo }} фото.
                <template v-if="infoTop36.photo === totalImg">
                    Изображений в вашей карточке достаточно!
                </template>
                <template v-else>Изображений в вашей карточке больше, все отлично!</template>
            </SeAlert>
        </template>

        <div class="fa-prev-img">
            <VImg
                v-for="img in imagesList"
                :key="img || 'https://i.stack.imgur.com/GNhxO.png'"
                lazy-src="https://i.stack.imgur.com/GNhxO.png"
                :src="img"
                aspect-ratio="1"
                class="mr-1 mb-1"
                max-width="72"
            ></VImg>
        </div>
    </div>
</template>

<script>
    import { getDeclWord } from '~utils/helpers';
    /* eslint-disable */
    import { mapGetters, mapState } from 'vuex';

    export default {
        name: 'MediaProduct',
        props: {
            values: {
                require: true,
                type: Object,
                default: null,
            },
            recommendations: {
                require: true,
                type: Array,
                default: () => [],
            },
            nomenclature: {
                type: [Object, Boolean],
                default: false,
            },
        },
        data() {
            return {
                popovers: {
                    video: false,
                },
                imageRules: {
                    wildberries: {
                        images: {
                            min: 1,
                            max: 30,
                        },
                        images360: {
                            min: 1,
                            max: 12,
                        },
                    },
                    ozon: {
                        images: {
                            min: 1,
                            max: 15,
                        },
                        images360: {
                            min: 15,
                            max: 70,
                        },
                    },
                },
                form: {
                    fields: {
                        images: [],
                        images360: [],
                        youtubecodes: '',
                    },
                    rules: {
                        youtubecodes: [
                            val =>
                                !val ||
                                /^[a-zA-Z0-9_\-:"()]*?$/.test(val) ||
                                'Некорректное значение',
                        ],
                        videoLinkWB: [
                            val => {
                                if (val?.length) {
                                    const extension = val
                                        .split(/[#?]/)[0]
                                        .split('.')
                                        .pop()
                                        .trim()
                                        .toLowerCase();
                                    return (
                                        extension === 'mp4' ||
                                        extension === 'mov' ||
                                        'Доступные форматы: MP4 и MOV'
                                    );
                                }
                                return true;
                            },
                        ],
                        externalLink: [
                            val =>
                                !val ||
                                /(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/.test(
                                    val
                                ) ||
                                'Некорректное значение',
                        ],
                    },
                },
                colors: {
                    tooltipIconColor: '#a6afbd',
                },
            };
        },
        computed: {
            ...mapState('product', ['selectedWbNmID']),
            ...mapGetters(['isSelectedMp']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
            }),
            ...mapState('product', ['infoTop36']),
            ...mapGetters('product', ['getProduct', 'getProdGrade', 'showRecom', 'getWbImages']),
            imagesList() {
                try {
                    if (this.isSelectedMp.id === 2) {
                        return Object.values(this.getWbImages)[this.selectedWbNmID];
                    } else {
                        const { images } = this.getProduct;
                        return images;
                    }
                } catch {
                    return [];
                }
            },

            showError() {
                try {
                    return this.infoTop36.photo - this.totalImg > 0;
                } catch {
                    return true;
                }
            },

            totalImg() {
                try {
                    return this.imagesList.length;
                } catch {
                    return 0;
                }
            },

            getSaveChangesMethod() {
                if (this.marketplaceSlug === 'wildberries') {
                    return this.saveChangeWildberries;
                } else {
                    return this.saveChangeOzon;
                }
            },
        },
        created() {
            try {
                if (this.isSelectedMp.id === 2) {
                    const {
                        data: { nomenclatures },
                    } = this.getProduct;

                    const { addin } = nomenclatures[0];
                    const photo = addin.find(({ type }) => type === 'Фото');
                    this.form.fields.images = photo.params.map(({ value }) => value);
                } else {
                    const { images } = this.getProduct;
                    this.form.fields.images = images;
                }
            } catch (error) {
                console.error(error);
            }
        },
        mounted() {
            this.$store.commit('product/setSignalAlert', { field: 'photo', value: this.showError });
        },
        methods: {
            showDialog(key, value = []) {
                const val = value !== null && value !== '' ? value : [];
                this.$refs.uploadDialog.show(key, val);
            },
            saveChangeOzon(key, value) {
                let changeTitle;
                const keyTitle = key === 'images' ? ' фото' : ' "фото 360"';
                this.form.fields[key] = value;

                let delta = this.form.fields[key].length;
                if (this.oldValues[key]) {
                    delta -= this.oldValues[key].length;
                }

                if (delta > 0) {
                    changeTitle = 'Добавлено ' + delta + keyTitle;
                } else if (delta < 0) {
                    changeTitle = 'Удалено ' + Math.abs(delta) + keyTitle;
                } else {
                    changeTitle = 'Обновлен список' + keyTitle;
                }

                this.onChange(key, changeTitle);
            },
            getDeclrImg(num) {
                return getDeclWord(['изображение', 'изображения', 'изображений'], num);
            },
            saveChangeWildberries(key, value) {
                let changeTitle;
                const keyTitle = key === 'images' ? ' фото' : ' "фото 360"';
                this.form.fields[key] = value;

                const delta = this.form.fields[key].length - this.oldValues?.[key]?.length;

                if (delta > 0) {
                    changeTitle =
                        'Добавлено ' +
                        delta +
                        keyTitle +
                        ' для номенклатуры ' +
                        this.nomenclature.name;
                } else if (delta < 0) {
                    changeTitle =
                        'Удалено ' +
                        Math.abs(delta) +
                        keyTitle +
                        ' из номенклатуры ' +
                        this.nomenclature.name;
                } else {
                    changeTitle =
                        'Обновлен список' +
                        keyTitle +
                        ' для номенклатуры ' +
                        this.nomenclature.name;
                }

                this.onChange(`nomenclature.${this.nomenclature.index}.${key}`, changeTitle, key);
            },
        },
    };
</script>

<style lang="scss">
    .fa-prev-img {
        display: flex;
        flex-wrap: wrap;
    }

    .product-options__media-item .default-btn {
        margin-right: 16px;
    }
</style>

<style lang="scss" module>
    /* stylelint-disable declaration-no-important */
    .MediaProduct {
        .MediaProductForm {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            grid-template-rows: none;
            grid-auto-flow: row dense;
            grid-gap: 16px 4.125rem;

            .Item {
                display: flex;
                flex-direction: column;
                gap: 8px;

                .ItemHeader {
                    font-size: 1rem;
                    font-weight: 500;
                    line-height: 1;

                    //@include respond-to(md) {
                    //    font-size: 1rem;
                    //}
                }

                .ItemBody {
                    display: flex;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 16px;
                }
            }
        }

        .headerFlex {
            display: inline-flex !important;
        }

        .button {
            @include respond-to(md) {
                width: 100%;
            }
        }
    }
</style>
