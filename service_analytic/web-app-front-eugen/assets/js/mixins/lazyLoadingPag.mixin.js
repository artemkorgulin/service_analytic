// import { _l } from 'lodash';
export default {
    data() {
        return {
            pageLoading: false,
            pageSetting: {
                perPage: 25,
                page: 1,
                search: '',
            },
            lastPage: undefined,
        };
    },
    watch: {},
    async mounted() {
        this.$el.addEventListener('scroll', this.handlerScroll);
    },
    beforeDestroy() {
        this.$el.removeEventListener('scroll', this.handlerScroll);
    },
    methods: {
        nextPage() {
            this.pageSetting.page += 1;
            try {
                this.defRequest();
            } catch (error) {
                console.error(error);
                throw new Error('Standard request not assigned');
            }
        },
        handlerScroll(e) {
            const el = this.$el;
            const isNotLastPage = this.lastPage && this.pageSetting.page !== this.lastPage;

            if (
                isNotLastPage &&
                !this.pageLoading &&
                el.scrollTop + el.clientHeight >= el.scrollHeight
            ) {
                this.nextPage();
            }
        },
    },
};
