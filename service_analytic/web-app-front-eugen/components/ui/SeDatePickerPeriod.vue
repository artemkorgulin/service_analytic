<template>
    <div class="se-date-picker-period">
        <ElSelect
            v-model="selectedPeriod"
            placeholder="Период"
            class="se-date-picker-period__select-period"
            @change="handleSelectedPeriod()"
        >
            <ElOption
                v-for="period in periods"
                :key="period.value"
                :label="period.label"
                :value="period.value"
            ></ElOption>
        </ElSelect>
        <ElDatePicker
            ref="picker"
            v-model="selectedDates"
            :picker-options="pickerOptions"
            class="se-date-picker-period__date-picker"
            type="daterange"
            align="right"
            format="d MMM yyyy"
            @change="handleSelectedDates()"
        />
    </div>
</template>
<script>
    // TODO: Нужен рефактор компонента
    import { format } from 'date-fns';
    import moment from 'moment';

    export default {
        name: 'SeDatePickerPeriod',
        props: {
            value: {
                type: Array,
                default: null,
            },
            userDates: {
                type: Array,
                default: null,
            },
        },
        data: () => ({
            selectedPeriod: 29,
            selectedDates: null,
            periods: [
                { label: '1 день', value: 0 },
                { label: '7 дней', value: 6 },
                { label: '30 дней', value: 29 },
                { label: '90 дней', value: 89 },
                { label: '365 дней', value: 364 },
            ],

            pickerOptions: {
                disabledDate(value) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return Number(new Date(value)) >= Number(today);
                },
                firstDayOfWeek: 1,
            },
        }),

        watch: {
            userDates: {
                handler(newVal) {
                    if (newVal) {
                        this.selectedDates = newVal;
                        const start = moment(newVal[0], 'YYYY-MM-DD');
                        const end = moment(newVal[1], 'YYYY-MM-DD');
                        const diffPeriod = Math.floor(end.diff(start, 'days', true));
                        const diffPeriodIndex = this.periods.findIndex(
                            ({ value }) => value === diffPeriod
                        );
                        this.selectedPeriod =
                            diffPeriodIndex !== -1 ? this.periods[diffPeriodIndex]['label'] : null;
                    }
                },
                deep: true,
            },
        },

        mounted() {
            if (this.$route.query?.date) {
                const date = this.$route.query?.date.split('=');
                this.selectedDates = [new Date(date[0]), new Date(date[1])];
                this.setPeriod();
            } else {
                // TODO для чего нам тут две функции, мне пришлось их вызвать все, чтобы поменять дату на дефолтную
                this.handleSelectedPeriod();
                this.handleSelectedDates();
                this.setPeriod();
            }
            this.$emit('input', [
                format(new Date(this.selectedDates[0]), 'yyyy-MM-dd'),
                format(new Date(this.selectedDates[1]), 'yyyy-MM-dd'),
            ]);
        },

        methods: {
            handleSelectedDates() {
                if (this.selectedDates === null) {
                    this.selectedPeriod = null;
                } else {
                    const [start, end] = this.selectedDates;
                    this.setPeriod();
                    this.$router.push({
                        query: {
                            ...this.$route.query,
                            date: `${format(start, 'yyyy-MM-dd')}=${format(end, 'yyyy-MM-dd')}`,
                        },
                    });
                    this.$emit('change', [
                        format(new Date(start), 'yyyy-MM-dd'),
                        format(new Date(end), 'yyyy-MM-dd'),
                    ]);
                }
            },

            handleSelectedPeriod() {
                if (this.selectedPeriod !== null) {
                    if (this.selectedDates === null) {
                        this.selectedDates = [new Date(), new Date()];
                    }
                    const end = new Date();
                    end.setDate(end.getDate() - 1);
                    const start = new Date(end);
                    start.setDate(end.getDate() - this.selectedPeriod);

                    this.selectedDates = [start, end];
                    this.$router.push({
                        query: {
                            ...this.$route.query,
                            date: `${format(start, 'yyyy-MM-dd')}=${format(end, 'yyyy-MM-dd')}`,
                        },
                    });
                    this.$emit('change', [format(start, 'yyyy-MM-dd'), format(end, 'yyyy-MM-dd')]);
                }
            },

            setPeriod() {
                const time =
                    (new Date(this.selectedDates[1]).getTime() -
                        new Date(this.selectedDates[0]).getTime()) /
                    86400000;
                const period = this.periods.find(item => item.value === time);
                this.selectedPeriod = period ? period.value : null;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .se-date-picker-period {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;

        &__select-period {
            max-width: 115px;
        }

        &__date-picker {
            min-width: 200px;
        }
    }
</style>
