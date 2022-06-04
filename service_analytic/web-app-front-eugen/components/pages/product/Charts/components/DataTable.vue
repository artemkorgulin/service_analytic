<template>
    <div>
        <TableContainer ref="table" class="mt-9">
            <THead>
                <slot name="thead" :column="tableColumns">
                    <TableHeadCell
                        v-for="(item, index) in tableColumns"
                        :key="`datatable-thead-th-${item.text}`"
                    >
                        <template v-if="index === 0">
                            <div>
                                <BaseCheckbox
                                    :value="isAllSelected"
                                    :indeterminate="isPartSelected"
                                    auto-size
                                    class="ma-0 pa-0"
                                    @click="toggleAllValues"
                                />
                                <span>{{ item.text }}</span>
                            </div>
                        </template>
                        <template v-else>
                            <span>{{ item.text }}</span>
                        </template>
                    </TableHeadCell>
                </slot>
            </THead>
            <TBody>
                <TableRow
                    v-for="(row, rowIndex) in tableRowWithPickList"
                    :key="`datatable-row-${rowIndex}`"
                    :class="[row.isPickedByUser ? 'picked' : '']"
                >
                    <TableBodyCell>
                        <div>
                            <div>
                                <BaseCheckbox
                                    :value="row.isSelected"
                                    auto-size
                                    class="ma-0 pa-0"
                                    @click="toggleValue(row)"
                                />
                                <span>{{ row.label }}</span>
                            </div>
                            <span
                                v-if="row.isSelected"
                                class="color"
                                :style="{ 'background-color': `${row.backgroundColor}` }"
                            />
                            <span v-else class="color" style="background-color: #fff" />
                        </div>
                    </TableBodyCell>
                    <slot name="tbody" :row="row" :index="rowIndex">
                        <TableBodyCell
                            v-for="(item, index) in row.replacedNull"
                            :key="`datatable-tbody-td-${index}`"
                        >
                            <span>{{ item }}</span>
                        </TableBodyCell>
                    </slot>
                </TableRow>
            </TBody>
        </TableContainer>
    </div>
</template>

<script>
    import { mapState } from 'vuex';
    import TableContainer from '~/components/ui/BaseTable/Table/components/TableContainer';
    import THead from '~/components/ui/BaseTable/Table/components/THead';
    import TBody from '~/components/ui/BaseTable/Table/components/TBody';
    import TableHeadCell from '~/components/ui/BaseTable/Table/components/TableHeadCell';
    import TableBodyCell from '~/components/ui/BaseTable/Table/components/TableBodyCell';
    import TableRow from '~/components/ui/BaseTable/Table/components/TableRow';
    import BaseCheckbox from '~/components/ui/BaseCheckbox';
    import { format } from 'date-fns';

    export default {
        name: 'DataTable',
        components: {
            TableContainer,
            THead,
            TBody,
            TableHeadCell,
            TableBodyCell,
            TableRow,
            BaseCheckbox,
        },
        props: {
            rows: { type: Array, default: null },
            columns: { type: Array, default: null },
            isScrollToEnd: {
                type: Boolean,
                default: true,
            },
        },
        data: () => ({
            selected: [],
            isSelectedTenTop: false,
        }),
        computed: {
            ...mapState({
                pickList: state => state.product.pickList,
            }),
            tableColumns() {
                const firstHeader = [
                    {
                        text: 'Ключевое слово',
                        sortable: false,
                        value: 'name',
                    },
                ];
                const headers = this.columns.map(label => {
                    const formattedLabel = format(new Date(label), 'd.MM');
                    return {
                        text: formattedLabel,
                        value: label,
                    };
                });
                return [...firstHeader, ...headers];
            },
            tableRowWithPickList() {
                if (this.pickList) {
                    const pickListKeywords = this.pickList.map(({ name }) => ({
                        label: name,
                        isSelected: true,
                        backgroundColor: '#fff',
                        replacedNull: Array(this.tableColumns.length - 1).fill('-'),
                        isPickedByUser: true,
                    }));
                    const setKeywords = new Set(this.tableRows);

                    return [
                        ...pickListKeywords.filter(({ label }) => !setKeywords.has(label)),
                        ...this.tableRows,
                    ];
                }

                return this.tableRows;
            },
            tableRows() {
                return this.rows.map(({ label, backgroundColor, data }) => {
                    const replacedNull = [];
                    data.forEach(val => {
                        if (val === null) {
                            replacedNull.push('-');
                        } else {
                            replacedNull.push(val);
                        }
                    });
                    return {
                        label,
                        backgroundColor,
                        replacedNull,
                        isPickedByUser: false,
                    };
                });
            },
            isPartSelected() {
                return (
                    this.selected.length > 0 &&
                    this.selected.length !== this.tableRowWithPickList.length
                );
            },
            isAllSelected() {
                return this.selected.length === this.tableRowWithPickList.length;
            },
        },
        watch: {
            tableRowWithPickList: {
                handler() {
                    if (this.isSelected10Top) {
                        this.tableRowWithPickList.forEach((item, index) => {
                            if (index < 10) {
                                item.isSelected = true;
                                this.addSelected(item);
                                this.$emit('update', this.selected);
                            }
                        });
                    }
                },
            },
        },
        mounted() {
            this.isSelected10Top = true;
            if (this.isScrollToEnd) {
                const table = this.$refs.table.$el;
                table.scrollLeft = table.scrollWidth;
            }
        },
        methods: {
            toggleValue(item) {
                this.isSelected10Top = false;
                item.isSelected = !item.isSelected;
                item.isSelected ? this.addSelected(item) : this.removeSelected(item);
                this.$emit('update', this.selected);
            },
            toggleAllValues() {
                if (!this.isAllSelected) {
                    this.tableRowWithPickList.forEach(item => {
                        item.isSelected = true;
                        this.addSelected(item);
                        this.$emit('update', this.selected);
                    });
                } else {
                    this.tableRowWithPickList.forEach(item => {
                        item.isSelected = false;
                        this.removeSelected(item);
                        this.$emit('update', this.selected);
                    });
                }
            },
            addSelected(item) {
                const index = this.selected.findIndex(({ label }) => item.label === label);
                if (index === -1) {
                    this.selected.push(item);
                }
            },
            removeSelected(item) {
                const index = this.selected.findIndex(({ label }) => item.label === label);
                if (index !== -1) {
                    this.selected.splice(index, 1);
                }
            },
        },
    };
</script>

<style scoped lang="scss">
    .picked {
        background: rgba(87, 116, 221, 0.08);
    }

    .color {
        display: block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .info {
        display: flex;
        align-items: center;
        margin-top: 16px;
        padding: 8px;
        border-radius: 8px;
        border: 1px solid #607fea;
        background: rgba(87, 116, 221, 0.08);
        font-size: 14px;
        color: #607fea;

        &_icon {
            width: 19px;
            min-width: 19px;
            height: 19px;
            min-height: 19px;
            margin-right: 10px;
        }
    }
</style>
