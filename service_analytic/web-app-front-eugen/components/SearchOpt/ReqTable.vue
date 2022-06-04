<template>
    <div class="sub-selection">
        <div
            class="sub-selection__statistic so-container pt-0 d-flex flex-wrap justify-space-between"
        >
            <div class="sub-selection__statistic-info">
                <span class="mr-3 mb-3">Выбрано: {{ pickList.length }} / 30</span>

                <span v-if="showErrorLimit" class="error--text">
                    Нельзя выбрать больше 30 ключевых слов
                </span>
            </div>
        </div>
        <perfect-scrollbar style="flex-grow: 1; height: 1px">
            <table class="so-table">
                <thead>
                    <tr>
                        <td
                            class="search-input"
                            :class="{ active: searchActive }"
                            style="width: 50%"
                            @click="$refs.keywordInput.focus()"
                        >
                            <div class="d-flex align-center">
                                <input
                                    ref="keywordInput"
                                    v-model="searchQuery"
                                    style="width: 90%"
                                    type="text"
                                    placeholder="Ключевые слова"
                                    @focus="searchActive = true"
                                    @blur="searchActive = false"
                                />
                                <SvgIcon
                                    name="outlined/search"
                                    style="height: 13px"
                                    color="#C8CFD9"
                                    class="search-input__btn"
                                />
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-center">
                                <input
                                    v-model="searchPopQuery"
                                    readonly
                                    placeholder="Популярность"
                                    style="width: 110px"
                                />
                            </div>
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(req, index) in filteredReqList" :key="'so-req-' + index">
                        <td class="so-table__title" @click="addOrRemItems(req)">{{ req.name }}</td>
                        <td class="so-table__checkbox">{{ req.popularity }}</td>
                    </tr>
                    <tr v-if="!filteredReqList.length">
                        <td colspan="100%" class="text-center">Ключевые слова отсутствуют</td>
                    </tr>
                </tbody>
            </table>
        </perfect-scrollbar>
    </div>
</template>

<script>
    /* eslint-disable */
    import { mapState, mapMutations } from 'vuex';
    export default {
        props: {
            reqList: {
                type: Array,
                default: () => [],
            },
        },
        data() {
            return {
                searchActive: false,
                addLimit: 30,
                searchQuery: '',
                searchPopQuery: '',
                showErrorLimit: false,
            };
        },
        computed: {
            ...mapState('product', ['pickList', 'commonData']),

            filteredReqList() {
                const res = this.reqList.filter(item => {
                    const reSq = new RegExp(this.searchQuery.toLowerCase());
                    const reSpq = new RegExp(this.searchPopQuery);
                    const name = item.name.toLowerCase();
                    const testSq = reSq.test(name);
                    const testPop = reSpq.test(item.popularity);
                    const isSelected = this.pickList.filter(el => el.name === item.name).length;

                    return testSq && testPop && item.popularity > 0 && !isSelected;
                });
                return res.sort((a, b) => b.popularity - a.popularity);
            },
        },
        watch: {
            pickList(value) {
                if (!value) return;
            },
        },
        methods: {
            ...mapMutations('product', ['setPickList']),
            addOrRemItems(item) {
                const { name } = item;
                const index = this.pickList.findIndex(item => item === name);

                const pickList = JSON.parse(JSON.stringify(this.pickList));

                if (index >= 0) {
                    pickList.splice(index, 1);
                } else {
                    if (this.addLimit < pickList.length + 1) {
                        this.showAlert();
                        return;
                    }
                    pickList.push(name);
                }

                this.setPickList(pickList);

                this.submit();
            },
            submit() {
                const res = [];

                this.reqList.forEach(item => {
                    if (this.pickList.includes(item.name)) {
                        const fO = {
                            ...item,
                            isActive: this.commonData.title.toLowerCase().includes(item.name),
                        };
                        res.push(fO);
                    }
                });

                this.$emit('update', res);
            },
            showAlert() {
                this.showErrorLimit = true;
                setTimeout(() => {
                    this.showErrorLimit = false;
                }, 2000);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .sub-selection {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .so-table {
        width: 100%;
        margin-bottom: 16px;
        border-spacing: 0;

        &__title {
            cursor: pointer;
            user-select: none;
        }

        thead tr td {
            position: sticky;
            top: 0;
            border-top: 1px solid $border-color;
            border-bottom: 1px solid $border-color;
            background: #fff;
        }

        tr td {
            padding: 12px;
            font-size: 14px;
        }
    }

    .search-input {
        &.active,
        &:hover {
            background: $base-400;
        }
    }

    .search-btn {
        cursor: pointer;
    }
</style>
