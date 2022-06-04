<template>
    <div class="search-opt" :style="`height: ${blockHeight}px`">
        <v-row no-gutters style="height: 100%">
            <v-col lg="4" sm="4" class="border-right" style="position: relative; height: 100%">
                <div class="sub-selection search-opt__sub-selection">
                    <div class="so-container">
                        <div class="search-opt__header">Тип товара</div>
                        <SeAlert class="mb-2">
                            Добавьте 3 типа товара, чтобы продвигаться в большем количестве запросов
                        </SeAlert>

                        <div class="se-cust-tf-fv mt-7" style="min-height: 64px">
                            <VAutocomplete
                                v-model="introducedTypes"
                                :search-input.sync="typeSearchInput"
                                multiple
                                :items="tipsList"
                                label="Тип товара"
                                class="light-inline"
                                chips
                                :error-messages="errorMess.introType"
                                :loading="isLoading.type"
                                @change="addTypeForSearchKeyQueries"
                            >
                                <template #selection="data">
                                    <v-chip
                                        color="primary"
                                        label
                                        close
                                        style="font-size: 12px"
                                        @click:close="delIntroducedTypes(data.index)"
                                    >
                                        {{ data.item }}
                                    </v-chip>
                                </template>
                                <template #item="data">
                                    <v-list-item-content>
                                        <v-list-item-title
                                            style="font-size: 14px"
                                            v-html="data.item"
                                        ></v-list-item-title>
                                    </v-list-item-content>
                                </template>
                                <template #append-outer>
                                    <SvgIcon
                                        name="outlined/search"
                                        style="height: 13px"
                                        class="search-input__btn mr-1"
                                    />
                                </template>
                            </VAutocomplete>
                        </div>
                    </div>
                    <div class="sub-selection__table" style="flex-grow: 1">
                        <ReqTable :req-list="reqList" @update="addKeyReq" />
                    </div>
                </div>
            </v-col>
            <v-col lg="4" sm="4" class="border-right" style="height: 100%">
                <div class="search-opt__key-selection">
                    <div class="so-container">
                        <div class="search-opt__header">Ключевые запросы</div>
                        <SeAlert class="mb-2">
                            Оставьте только те ключевые слова, которые хотите отслеживать
                        </SeAlert>
                    </div>
                    <div class="search-opt__req-list so-req-list">
                        <perfect-scrollbar style="flex-grow: 1">
                            <KeyWordItem
                                v-for="(keyReq, index) in selectedKeyReq"
                                :key="`keyReq${index}`"
                                :keyword-object="keyReq"
                                :index="index"
                                @click="addInField"
                                @del="delKeyWord"
                            ></KeyWordItem>
                            <div v-if="!selectedKeyReq.length" class="text-center">
                                Не добавлено ни одного запроса
                            </div>
                        </perfect-scrollbar>
                    </div>
                </div>
            </v-col>
            <v-col lg="4" style="height: 100%">
                <div class="search-opt__form">
                    <div class="so-container">
                        <div class="search-opt__header">Характеристики</div>
                        <SeAlert class="mb-2">
                            Выберите поле и добавьте ключевое слово из списка слева
                        </SeAlert>
                    </div>
                    <perfect-scrollbar class="key-word-form" style="flex-grow: 1">
                        <div class="so-container">
                            <KeyWordsForm
                                ref="keyWordForms"
                                :values="keywordsFormValues"
                                @getFieldValue="getFieldValue"
                            ></KeyWordsForm>
                        </div>
                    </perfect-scrollbar>
                </div>
            </v-col>
        </v-row>
    </div>
</template>

<script>
    import { mapMutations, mapGetters, mapState } from 'vuex';
    import ReqTable from '../SearchOpt/ReqTable.vue';
    /* eslint-disable  */
    export default {
        components: { ReqTable },
        props: {
            keywordsFormValues: {
                type: Object,
                default: () => ({}),
            },
        },
        data() {
            return {
                hintsMenu: true,
                isLoading: {
                    type: false,
                },
                reqList: [],
                selectedKeyReq: [],
                tipsList: [],
                typeSelBox: '',
                introducedTypes: [],
                typeSearchInput: '',
                errorMess: {
                    introType: '',
                },
            };
        },
        async mounted() {
            const { cat } = this.category;
            if (this.isSelectedMp.id === 2) {
                this.introducedTypes.push(cat);
                this.tipsList.push(cat);
                await this.getKeyRequestByType();
            }
            await this.getSubjectForSearch();
            this.getPickListLocal();
        },
        computed: {
            ...mapState('product', ['pickList', 'commonData']),
            ...mapGetters(['isSelectedMp']),
            ...mapGetters('product', ['getProduct']),

            category() {
                try {
                    if (this.isSelectedMp.id === 2) {
                        const { object: cat, parent: parentCat } = this.getProduct;
                        return { cat, parentCat };
                    } else {
                        const { web_category_name: cat, bread_crumbs } = this.getProduct;
                        return { cat: '', parentCat: bread_crumbs.split('-')[0].trim() };
                    }
                } catch {
                    return { cat: '', parentCat: '' };
                }
            },
            productId() {
                return Number(this.$route.params.id);
            },
            blockHeight() {
                const { innerWidth: x, innerHeight: y } = window;
                const stMar = 20;
                const headerEl = document.querySelector('.se-content-header');
                const prodActEl = document.querySelector('.product-actions');
                return y < 800
                    ? 1000
                    : y - headerEl.offsetHeight - prodActEl.offsetHeight - stMar * 2;
            },
        },
        methods: {
            ...mapMutations('product', ['setPickList']),
            getInputs() {
                return this.$refs.keyWordForms.getInputs();
            },
            savePickList(pickList) {
                if (!pickList.length) return;
                pickList = JSON.parse(JSON.stringify(pickList));

                this.setPickList(pickList);
            },
            getPickListLocal() {
                const res = [];
                this.pickList.forEach(item => {
                    const { name, popularity } = item;

                    res.push({
                        isActive: this.commonData.title.toLowerCase().includes(name),
                        name,
                        popularity,
                    });
                });

                this.selectedKeyReq = res;
            },
            async getSubjectForSearch() {
                const topic = '/api/vp/v1/user_categories';

                try {
                    const {
                        data: {
                            data: { all_subjects: tipsList },
                        },
                    } = await this.$axios.get(topic);

                    Object.keys(tipsList).forEach(_ => {
                        this.tipsList.push(tipsList[_]);
                    });
                } catch (error) {
                    console.error(error);
                }
            },
            async getKeyRequestByType() {
                const { parentCat } = this.category;
                this.isLoading.type = true;
                let topic = ['/api/vp/v2/key_requests', 'api/vp/v2/wildberries/key_requests'];

                topic = topic[this.isSelectedMp.id - 1];
                topic = `${topic}?category=${parentCat}`;

                this.introducedTypes.forEach(kr => {
                    topic += `&products[]=${kr}`;
                });

                try {
                    const {
                        data: { data },
                    } = await this.$axios.get(topic);

                    this.reqList = data;
                } catch (error) {
                    console.error(error);
                }
                this.isLoading.type = false;
            },
            async addTypeForSearchKeyQueries(e) {
                const { length: introTypeL } = this.introducedTypes;
                if (introTypeL > 3) {
                    this.introducedTypes.splice(introTypeL - 1, 1);
                    this.errorMess.introType = 'Нельзя выбрать больше 3-ех типов предметов';
                    return;
                }
                this.errorMess.introType = '';
                this.typeSearchInput = '';
                await this.getKeyRequestByType();
            },
            async delIntroducedTypes(i) {
                this.errorMess.introType = '';
                this.introducedTypes.splice(i, 1);
                if (!this.introducedTypes.length) {
                    this.reqList = [];
                    return;
                }
                await this.getKeyRequestByType();
            },
            addKeyReq(keyReqList) {
                const addedKw = this.selectedKeyReq.map(({ name }) => name);

                keyReqList.forEach(kw => {
                    if (!addedKw.includes(kw.name)) {
                        this.selectedKeyReq.push(kw);
                    }
                });

                this.savePickList(this.selectedKeyReq);
            },
            delKeyWord(kw) {
                const { name: nameKw } = kw;
                for (
                    let i = 0, selectedKeyReqLen = this.selectedKeyReq.length;
                    i <= selectedKeyReqLen;
                    i++
                ) {
                    const { name } = this.selectedKeyReq[i];
                    if (name === nameKw) {
                        this.selectedKeyReq.splice(i, 1);
                        this.savePickList(this.selectedKeyReq);

                        break;
                    }
                }
            },
            addInField(keyWord) {
                if (!keyWord.isActive) {
                    keyWord.isActive = true;
                    this.$refs.keyWordForms.addWordInField(keyWord.name);
                }
            },
            getFieldValue(value) {
                this.selectedKeyReq.forEach(el =>
                    value.toLowerCase().includes(el.name)
                        ? (el.isActive = true)
                        : (el.isActive = false)
                );
            },
        },
    };
</script>

<style lang="scss">
    .search-opt {
        border-radius: 16px;
        background: #fff;

        @include cardShadow2();

        &__header {
            @include inner-card-header();
        }

        &__sub-selection {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        &__key-selection {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        &__form {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
    }

    .sub-selection {
        &__statistic {
            font-size: 14px;
            font-weight: bold;
            color: $primary-color;

            &-info {
                display: flex;
                flex-direction: column;
                max-width: 50%;
            }
        }

        &__actions {
            position: absolute;
            bottom: 0;
            display: flex;
            width: 100%;
            border-top: 1px solid $border-color;
        }
    }

    .so-container {
        padding: 16px;
    }

    .border-right {
        border-right: 1px solid $border-color;
    }

    .se-form {
        &__item {
            margin-bottom: 16px;
            padding: 16px;
            border-radius: 8px;
            border: 2px solid $border-color;
            cursor: pointer;

            input,
            textarea {
                font-size: 14px;
                line-height: 1.7;
            }

            &_active {
                border-color: $primary-color;
            }
        }
    }
</style>
