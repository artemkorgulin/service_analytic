export default {
    computed: {
        isShowOnBoardingFromComp() {
            return this.$store.getters['onBoarding/isOnboardActive'];
        },
    },
    methods: {
        createOnBoardingByParams({
            elements,
            routeNameFirstStep,
            isDisplayOnboard,
            timeout = 500,
        }) {
            if (routeNameFirstStep) {
                this.$store.commit('onBoarding/setFirstStep', {
                    field: routeNameFirstStep,
                    value: elements,
                });
            }

            this.$store.commit('onBoarding/setActiveElements', elements);

            if (!isDisplayOnboard) {
                return;
            }

            setTimeout(() => {
                const isOnboardActive = this.isShowOnBoardingFromComp;

                if (isOnboardActive) {
                    this.$store.commit('onBoarding/setField', {
                        value: true,
                        field: 'activateOb',
                    });
                }
            }, timeout);
        },
        checkRouteForOnboarding() {
            const completedRoutes = JSON.parse(localStorage.getItem('completedRoutes')) || {};
            return Object.keys(completedRoutes).includes(this.$route.name);
        },
    },
};
