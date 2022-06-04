<template>
    <div style="width: 400px; min-width: 300px; padding: 20px" @keyDown="onKeyDown(e)">
        <v-autocomplete
            v-if="editMode === 'autocomplete'"
            ref="select"
            v-model="model"
            :items="items"
            item-text="title"
            multiple
            :return-object="isSelectedMp.id === 1"
            autofocus
            class="se-autocomplete se-bulk-edit-ac light-inline mt-0 pt-0"
            item-value="title"
            :search-input.sync="search"
            :error-messages="errorMessages"
            style="width: 100%"
            dense
            color="primary"
            :loading="isLoading"
            chips
            @change="checkModel"
            @focus="focusEl"
        >
            <template #selection="{ item, index }">
                <v-chip label small color="primary" close @click:close="remove(index)">
                    {{ item.title }}
                </v-chip>
            </template>
            <template #item="data">
                <div class="remote-search-output">
                    {{ data.item.title }}

                    <v-tooltip top>
                        <template #activator="{ on, attrs }">
                            <div
                                v-if="data.item.popularity && data.item.popularity !== null"
                                class="remote-search-output__popularity"
                                v-bind="attrs"
                                v-on="on"
                            >
                                {{ data.item.popularity }}
                            </div>
                        </template>
                        <span>популярность значения характеристики</span>
                    </v-tooltip>
                </div>
            </template>
        </v-autocomplete>
        <v-text-field
            v-else
            v-model="modelText"
            :counter="columnSetup.maxCount"
            :type="numberType ? 'number' : 'text'"
        ></v-text-field>
        <div class="popup__actions mt-3">
            <v-btn depressed snall class="se-btn" color="primary" @click="stopEditing">
                Сохранить
            </v-btn>
        </div>
    </div>
</template>

<script>
    import _l from 'lodash';
    import { mapGetters, mapState } from 'vuex';

    export default {
        data() {
            return {
                editMode: 'autocomplete',
                numberType: false,
                modelText: '',
                model: [],
                search: '',
                isLoading: false,
                value: undefined,
                columnSetup: undefined,
                category: undefined,
                type: undefined,
                items: [],
                errorMessages: '',
            };
        },
        computed: {
            ...mapState('product', ['closedRowForEdit']),
            ...mapGetters(['isSelectedMp', 'isSelMpIndex']),
        },
        watch: {
            async search() {
                await this.debGetHints();
            },
        },
        created() {
            /* eslint-disable */
            this.debGetHints = _l.debounce(this.getHints, 300);

            const {
                colDef,
                value,
                column: { colId },
                rowIndex,
            } = this.params;
            this.columnSetup = colDef.columnSetup;

            const { required } = this.columnSetup;
            const copyMode = this.columnSetup.mode === 'copy';
            const isBooleanTypeCell = this.columnSetup.editingMethod === 'boolean';
            const isCloseForEdit = this.closedRowForEdit.includes(rowIndex);

            if (
                isCloseForEdit ||
                (copyMode && ((required && Boolean(value)) || !required)) ||
                isBooleanTypeCell
            ) {
                this.columnSetup.gridApi.stopEditing();
                return;
            }

            this.category = colDef.category;
            const { dictionary: dir } = this.columnSetup;

            try {
                if (this.isSelectedMp.id === 2 && dir) {
                    try {
                        this.model = value;
                        this.items = value.map(_ => ({ title: _, popularity: null }));
                    } catch (error) {
                        console.error(error);
                    }
                } else if (this.columnSetup.editingMethod !== 'default') {
                    const numberTypes = ['integer', 'decimal'];
                    this.editMode = 'text';
                    this.numberType = numberTypes.includes(this.columnSetup.editingMethod);
                    this.modelText = value;
                } else {
                    this.model = [...value];
                    this.items = [...value];
                }
            } catch (error) {
                console.error(error);
            }

            this.type = colId;
        },
        mounted() {
            if (this.editMode === 'autocomplete') this.customFocus();
        },
        methods: {
            focusEl(e) {
                this.errorMessages = '';
            },
            stopEditing() {
                this.params.stopEditing();
            },
            /* eslint-disable */
            customFocus() {
                if (this.columnSetup.useOnlyDictionaryValues) {
                    return;
                }

                const input = this.$refs.select.$el.querySelector('input');

                const keyPressEnter = e => {
                    const { key } = e;
                    if (key !== 'Enter') return;
                    pasteValue();
                };

                const pasteValue = () => {
                    if (!input.value || this.model.includes(input.value)) return;
                    this.items.push({
                        title: input.value,
                        popularity: null,
                    });
                    this.model.push(input.value);
                    this.search = '';
                };

                input.addEventListener('keypress', keyPressEnter);
                input.addEventListener('blur', e => {
                    pasteValue();
                    input.removeEventListener('keypress', keyPressEnter);
                });
            },
            checkModel(e) {
                const { maxCount } = this.columnSetup;

                if (maxCount && this.model.length > maxCount) {
                    this.model.splice(maxCount);
                    this.errorMessages = `Максимальное кол-во элементов ${maxCount}`;
                    return;
                }

                this.search = '';
            },
            onKeyDown(e) {
                e.stopPropagation();
            },
            async getHints() {
                this.isLoading = true;
                const { dictionary: dir, id } = this.columnSetup;
                const topic = [
                    `/api/vp/v2/oz/directories/${id}`,
                    `/api/vp/v2/wildberries/directories${dir}`,
                ][this.isSelMpIndex];

                const params = {
                    search: this.search,
                    type: this.type,
                    category: this.category,
                };
                try {
                    let { data } = await this.$axios.get(topic, { params });

                    if (this.isSelectedMp.id === 2) {
                        data = data.items.data;
                    } else {
                        data = data.data;
                    }

                    const alreadyExistingValues = this.items.map(({ title }) => title);

                    for (const item of data) {
                        if (!alreadyExistingValues.includes(item.title)) {
                            const getO = {
                                title: item[['value', 'title'][this.isSelMpIndex]],
                                popularity: item.popularity,
                            };

                            this.isSelectedMp.id === 1 && (getO.id = item.pivot.option_id);
                            this.items.push(getO);
                        }
                    }
                } catch (err) {
                    console.error(err);
                } finally {
                    this.isLoading = false;
                }
            },
            remove(i) {
                this.model.splice(i, 1);
            },
            getValue() {
                if (this.editMode !== 'autocomplete') {
                    return this.modelText;
                } else {
                    return this.model;
                }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .popup {
        &__actions {
            display: flex;
            justify-content: flex-end;
        }
    }
</style>
