<template>
    <div class="user-plan">
        <div class="user-plan__header pa-4">
            <h3>Подключенный тариф</h3>
            <div v-if="false">
                <NuxtLink to="/tariffs">Изменить</NuxtLink>
            </div>
        </div>
        <div>
            <div class="service">
                <ul class="service__list mt-4 mb-4">
                    <template v-for="item in currentTariffServices">
                        <li v-if="isServiceShown(item)" :key="item.id" class="service__item">
                            <SvgIcon
                                name="outlined/tick"
                                class="service__icon mr-2"
                                style="width: 16px"
                            />
                            <span>{{ item.name }}</span>
                        </li>
                    </template>
                    <li class="service__item">
                        <SvgIcon
                            name="outlined/tick"
                            class="service__icon mr-2"
                            style="width: 16px"
                        />
                        <span>{{ pluralizedProductsNumber }}</span>
                    </li>
                </ul>
            </div>
            <div v-if="userPlan.paid" class="user-plan__br pa-4">
                <div class="user-plan__info-item">
                    <div class="user-plan__title">Активен до:</div>
                    <div class="user-plan__value">{{ $options.filters.formatDateTime(userPlan.expDate, '$d.$m.$y') }}</div>
                </div>
                <div class="user-plan__info-item">
                    <div class="user-plan__title">Сумма:</div>
                    <div class="user-plan__value">{{ parseInt(userPlan.price) | divByCat }}₽</div>
                </div>
                <div class="user-plan__info-item">
                    <div class="user-plan__title">Способ оплаты:</div>
                    <div class="user-plan__value">{{ userPlan.typePayment }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import formatText from '~mixins/formatText.mixin';

    import { mapGetters } from 'vuex';
    export default {
        mixins: [formatText],
        props: {
          servicesSortingArray: {
              required: false,
              default: null,
          },
        },
        data() {
            return {
                currentTariffServices: [],
                freeTariffDescription: [
                    {
                        alias: 'analytics',
                        id: 5,
                        name: 'Аналитика маркетплейсов',
                    },
                    {
                        alias: 'depo',
                        id: 2,
                        name: 'Депонирование',
                    },
                ],
            };
        },
        computed: {
            ...mapGetters(['userPlan']),
            isProdOrDemo() {
                return ['prod', 'demo'].includes(process.env.SERVER_TYPE);
            },
            pluralizedProductsNumber() {
                return (
                    this.userPlan?.sku +
                    ' ' +
                    this.$options.filters.plural(this.userPlan?.sku, [
                        'товар',
                        'товарa',
                        'товаров',
                    ])
                );
            },
        },
        async created() {
            await this.getCurrentTariffInfo();
        },
        methods: {
            async getCurrentTariffInfo() {
                const topic = '/api/v2/billing/information';

                try {
                    const { data } = await this.$axios.get(topic);
                    if (data.data?.is_free) {
                        this.currentTariffServices = this.freeTariffDescription;
                    } else if (data.data?.services) {
                        const { services } = data.data;

                        if (this.servicesSortingArray) {
                            services.sort((a, b) => this.servicesSortingArray.indexOf(a.alias) > this.servicesSortingArray.indexOf(b.alias) ? 1 : this.servicesSortingArray.indexOf(b.alias) > this.servicesSortingArray.indexOf(a.alias) ? -1 : 0);
                        }

                        this.currentTariffServices = services;
                    }
                } catch (error) {
                    console.error(error);
                }
            },
            isServiceShown(item) {
                const disabledItems = this.isProdOrDemo ? ['ad', 'depo'] : ['ad'];
                return !disabledItems.includes(item.alias);
            },
        },
    };
</script>

<style lang="scss" scoped>
    .user-plan {
        border-radius: $inner-radius;
        //border: 1px solid $border-color;

        &__title {
            font-size: 14px;
            color: $base-700;
        }

        &__info-item {
            margin-bottom: 8px;

            &:last-child {
                margin-bottom: 0;
            }
        }

        &__value {
            margin-top: 3px;
            font-size: 20px;
        }

        &__header {
            display: flex;
            justify-content: space-between;
            color: $primary-500;

            @include subtitle-1;
        }

        &__br {
            border-top: 1px solid $border-color;
        }
    }

    .service {
        &__list {
            margin: 0;
            padding: 0;
            padding-left: 16px;
            font-size: 16px;
        }

        &__item {
            display: flex;
            align-items: center;
            min-height: 24px;
            margin-bottom: 8px;
        }
    }
</style>
