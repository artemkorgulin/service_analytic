<template>
    <div class="dashboard-card dashboard-tariff" :class="borderClass">
        <div class="dashboard-tariff-header">
            <h3 class="dashboard-card__header dashboard-tariff-header__header">Подключенный тариф</h3>
            <div class="dashboard-tariff__button">
                <NuxtLink to="/tariffs">Изменить</NuxtLink>
            </div>
        </div>
        <div class="dashboard-tariff-subheader">
            <div class="dashboard-tariff-subheader__item info-block">
                <div class="info-block__label">
                    Тариф:
                </div>
                <div class="info-block__value font-weight-bold" :class="colorClassHeader">
                    {{ userPlan.nameRate }}
                </div>
            </div>
            <div class="dashboard-tariff-subheader__item info-block">
                <div class="info-block__label">
                    Активен до:
                </div>
                <div class="info-block__value">
                    {{ userPlan.expDate.length ? $options.filters.formatDateTime(userPlan.expDate, '$d.$m.$y') : '-' }}
                </div>
            </div>
        </div>
        <VDivider />
        <ul class="dashboard-tariff-features">
            <li class="dashboard-tariff-features__item">
                <SvgIcon
                    name="outlined/tick"
                    class="mr-2"
                    style="width: 16px"
                />
                <span>Оптимизация карточек - <span :class="colorClass">{{ skuString }}</span></span>
            </li>
            <li class="dashboard-tariff-features__item">
                <SvgIcon
                    name="outlined/tick"
                    class="mr-2"
                    style="width: 16px"
                />
                <span>Аналитика</span>
            </li>
            <li v-if="isAdShown" class="dashboard-tariff-features__item">
                <SvgIcon
                    name="outlined/tick"
                    class="mr-2"
                    style="width: 16px"
                />
                <span>Реклама</span>
            </li>
            <li v-if="!isProdOrDemo" class="dashboard-tariff-features__item">
                <SvgIcon
                    name="outlined/tick"
                    class="mr-2"
                    style="width: 16px"
                />
                <span>Депонирование - {{ remainLimit }}/{{ totalLimit }}</span>
            </li>
        </ul>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    export default {
        name: 'DashboardTariff',
        data() {
            return {
                isMounted: false,
                tarif: {},
                remainLimit: 0,
                totalLimit: 0,
            };
        },
        computed: {
            ...mapGetters(['userPlan']),
            colorClass() {
                return this.userPlan.paid ? 'info-block_color-green' : 'info-block_color-red';
            },
            colorClassHeader() {
                return this.userPlan.paid ? 'info-block_color-green' : null;
            },
            borderClass() {
                return this.userPlan.paid ? null : 'dashboard-tariff_red-border';
            },
            skuString() {
                const number = this.userPlan.sku;
                const products = this.$options.filters.plural(number, [
                    'товар',
                    'товара',
                    'товаров',
                ]);
                return `${number} ${products}`;
            },
            isProdOrDemo() {
                return ['prod', 'demo'].includes(process.env.SERVER_TYPE);
            },
            isAdShown() {
                 return this.userPlan.paid;
            },
        },
        created() {
            this.getEscrowLimits();
        },
        mounted() {
            this.isMounted = true;
        },
        methods: {
            async getEscrowLimits() {
                try {
                    const topic = '/api/vp/v2/escrow/limits';
                    const { data: { data: { remainLimit, totalLimit } } } = await this.$axios.get(topic);
                    this.remainLimit = remainLimit || 0;
                    this.totalLimit = totalLimit || 0;
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>

<style lang='scss' scoped>
    .dashboard-tariff {
        @include flex-grid-y;

        &-header {
            display: flex;
            justify-content: space-between;

            @include respond-to(md) {
                padding-bottom: 1rem;
                border-bottom: 1px $color-gray-light solid;

                & :global(.dashboard-card__header) {
                    font-size: 1rem !important;
                    font-weight: 600 !important;
                }
            }

            &__header {
                margin-bottom: 0 !important;
            }
        }

        &-subheader {
            @include flex-grid-x;

            &__item {
                flex-grow: 1;
            }
        }

        &-features {
            margin: 0;
            padding: 0;
            padding-left: 16px;
            font-size: 15px;
            line-height: 22px;
            font-weight: 500;

            &__item {
                display: flex;
                align-items: center;
                min-height: 24px;
                margin-bottom: 8px;
            }
        }

        &.dashboard-tariff_red-border {
            border: 1px solid #FF3981;
            box-shadow: 0 4px 32px rgba(0, 0, 0, 0.06);
        }

        &__button {
            font-weight: bold;
            font-size: 16px;
            line-height: 22px;
            color: #710bff;
            transition: $primary-transition;
            cursor: pointer;
            user-select: none;

            &:hover {
                opacity: 0.7;
            }
        }
    }

    .info-block {
        &__label {
            font-size: 12px;
            line-height: 16px;
            color: $color-gray-dark;
        }

        &__value {
            font-size: 16px;
            line-height: 24px;
        }
    }

     .info-block_color-green {
        color: $color-green-secondary;
    }

     .info-block_color-red {
        color: $error;
    }
</style>
