import { mapActions, mapGetters, mapMutations } from 'vuex';
import { format } from 'date-fns';

export default {
    data: () => ({
        periods: [
            { label: '1 день', value: 'yesterday' },
            { label: '7 дней', value: 'week' },
            { label: '30 дней', value: 'month' },
            { label: '90 дней', value: 'quarter' },
            { label: '365 дней', value: 'year' },
        ],
        selectedDates: [],
        selectedPeriod: null,
        chartParams: null,
    }),
    computed: {
        ...mapGetters(['isSelectedMp']),
    },
    watch: {
        selectedPeriod(val) {
            if (val) {
                const end = new Date();
                const start = new Date(end);
                start.setDate(start.getDate() - 1);
                end.setDate(end.getDate() - 1);
                switch (val) {
                    case 'yesterday':
                        start.setDate(start.getDate() - 1);
                        break;
                    case 'week':
                        start.setDate(start.getDate() - 7);
                        break;
                    case 'month':
                        start.setDate(start.getDate() - 30);
                        break;
                    case 'quarter':
                        start.setDate(start.getDate() - 90);
                        break;
                    case 'year':
                        start.setDate(start.getDate() - 365);
                        break;
                }
                this.selectedDates = [start, end];
            }
        },
    },
    created() {
        this.selectedPeriod = 'month';
    },
    methods: {
        ...mapActions({
            fetchDataChartWB: 'marketPlaceChart/fetchDataChartWB',
        }),
        ...mapMutations('marketPlaceChart', ['setField']),
        handlePeriod(val) {
            try {
                if (val && val.length > 0) {
                    const [startDate, endDate] = val;
                    const urlParams = new URLSearchParams();
                    urlParams.append('start_date', this.formatDate(startDate));
                    urlParams.append('end_date', this.formatDate(endDate));
                    this.chartParams = urlParams;
                    this.setField({ field: 'datesStartEnd', value: val });
                } else {
                    this.chartParams = new URLSearchParams();
                }
            } catch (error) {
                console.error(error);
            }
        },
        formatDate(date) {
            return format(date, 'yyyy-MM-dd');
        },
    },
};
