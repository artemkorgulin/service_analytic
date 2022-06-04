<template>
    <div class="table-footer">
        <div v-for="tableItem in tableData" :key="tableItem.key" class="table-footer-data">
            <span class="table-footer-data__label">{{ tableItem.label }}</span>
            <span class="table-footer-data__value">{{ tableItem.value }}</span>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'Statistics',
        props: {
            tableValues: {
                type: Object,
                default: null,
            },
        },
        data: () => ({
            tableLabels: [
                // { label: 'Выручка Итого, руб:', key: 'revenue', value: 0 },
                { label: 'Ср. значение:', key: 'avg', value: 0 },
                { label: 'Количество:', key: 'count', value: 0 },
                { label: 'Мин:', key: 'min', value: 0 },
                { label: 'Макс:', key: 'max', value: 0 },
                { label: 'Сумма:', key: 'sum', value: 0 },
            ],
        }),
        computed: {
            tableData() {
                if (this.tableValues) {
                    return this.tableLabels.map(val => ({
                        ...val,
                        value: this.tableValues[val.key],
                    }));
                }
                return this.tableLabels;
            },
        },
    };
</script>

<style scoped lang="scss">
    .table-footer {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 10px;

        &-data {
            display: flex;
            gap: 4px;
            font-size: 12px;
            line-height: 1.33;

            &__label {
                color: $base-700;
            }

            &__value {
                font-weight: bold;
                color: $base-900;
            }
        }
    }
</style>
