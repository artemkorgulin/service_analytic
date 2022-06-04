<template>
    <div class="prod-content-rat">
        <template v-if="showRecom">
            <SeAlert v-if="showError" type="alert" class="mb-2">
                Минимальная оценка аналогичного товара у конкурентов с первой страницы в категории -
                {{ infoTop36.rating }}. Необходимо собрать
                {{ recomReviews }}
                {{ getDeclRew(infoTop36.comments - getProdGrade.reviews) }} для товара. Стимулируйте
                покупателей оставлять больше отзывов на товар
            </SeAlert>
            <SeAlert v-else type="success" class="mb-2">
                Минимальная оценка аналогичного товара у конкурентов с первой страницы в категории -
                {{ infoTop36.rating }}.
                <template v-if="infoTop36.rating === getProdGrade.grade">
                    Рейтинг вашего товара такой же, как и у конкурентов!
                </template>
                <template v-else>Рейтинг вашего товара - выше, все отлично!</template>
            </SeAlert>
        </template>
        <div class="prod-content-rat__content">
            <div class="prod-content-rat__prod-rat prod-rat">
                <span class="prod-content-rat__subtitle subtitle-3">Ваш рейтинг</span>
                <span class="prod-rat__grade">{{ getProdGrade.grade | repDotWithCom }}</span>
                <!-- <SvgIcon name="common/ratStarFilled" style="height: 30px"></SvgIcon> -->
                <div class="prod-rat__stars rat-stars d-flex justify-center mt-2">
                    <div v-for="n in 5" :key="`rs${n}`" class="rat-stars_star">
                        <img
                            v-if="n <= getProdGrade.grade"
                            src="~assets/sprite/svg/common/ratStarFilled.svg"
                            style="width: 24px"
                            :class="{ 'mr-1': n !== 5 }"
                        />
                        <img
                            v-else
                            src="~assets/sprite/svg/common/ratStar.svg"
                            style="width: 24px"
                            :class="{ 'mr-1': n !== 5 }"
                        />
                    </div>
                </div>
                <div class="prod-rat__reviews">
                    {{ getProdGrade.reviews }} {{ getDeclRew(getProdGrade.reviews) }}
                </div>
            </div>
            <div class="prod-content-rat__req">
                <span class="prod-content-rat__subtitle subtitle-3">У конкурентов в ТОП-36:</span>
                <div class="prod-content-rat__req_content">
                    <div
                        class="prod-content-rat__badge rat-badge mb-2"
                        :class="calcStyleRate.minGrade.type"
                    >
                        <span class="rat-badge__text">Минимальный рейтинг</span>
                        <span class="rat-badge__grade">
                            {{ Number(infoTop36.rating).toFixed(1) | repDotWithCom }}
                        </span>
                        <SvgIcon
                            :name="calcStyleRate.minGrade.icon"
                            :class="calcStyleRate.minGrade.class"
                        ></SvgIcon>
                    </div>
                    <div
                        class="prod-content-rat__badge rat-badge"
                        :class="calcStyleRate.minReviews.type"
                    >
                        <span class="rat-badge__text">Минимум отзывов</span>
                        <span class="rat-badge__grade">{{ infoTop36.comments }}</span>
                        <SvgIcon
                            :name="calcStyleRate.minReviews.icon"
                            :class="calcStyleRate.minReviews.class"
                        ></SvgIcon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapState, mapGetters } from 'vuex';
    import formatText from '~mixins/formatText.mixin';
    export default {
        mixins: [formatText],
        computed: {
            ...mapState('product', ['infoTop36']),
            ...mapGetters('product', ['getProduct', 'getProdGrade', 'showRecom']),

            recomReviews() {
                /* eslint-disable */
                const rez =
                    (this.getProdGrade.reviews *
                        (this.infoTop36.rating - this.getProdGrade.grade)) /
                    (5 - this.infoTop36.rating);
                return rez == 0 ? 1 : Math.ceil(rez);
            },

            showError() {
                return this.infoTop36.rating > this.getProdGrade.grade;
            },
            calcStyleRate() {
                /* eslint-disable */
                const { rating: gradeTop36 = 0, comments: revTop36 = 0 } = this.infoTop36;
                const { reviews, grade } = this.getProdGrade;
                // TODO Перетащить в глобальные константы
                const sucIcon = 'filled/checkoutlined';
                const warIcon = 'filled/warningoutlined';
                const sucClass = 'suc-icon';
                const warClass = 'warning-icon';

                const getIconAndClass = condition => {
                    const res = condition
                        ? [sucIcon, sucClass, 'success']
                        : [warIcon, warClass, 'alert'];

                    return res;
                };

                const getData = arr => ({
                    icon: arr[0],
                    class: arr[1],
                    type: arr[2],
                });

                return {
                    minGrade: getData(getIconAndClass(gradeTop36 <= grade)),
                    minReviews: getData(getIconAndClass(revTop36 <= reviews)),
                };
            },
        },
        mounted() {
            this.$store.commit('product/setSignalAlert', { field: 'grade', value: this.showError });
        },
        methods: {
            getDeclRew(value) {
                const words = ['отзыв', 'отзыва', 'отзывов'];
                value = Math.abs(value) % 100;
                const num = value % 10;
                if (value > 10 && value < 20) return words[2];
                if (num > 1 && num < 5) return words[1];
                if (num == 1) return words[0];
                return words[2];
            },
        },
    };
</script>

<style lang="scss" scoped>
    .prod-content-rat {
        &__subtitle {
            display: block;
            margin-bottom: 16px;
            font-size: 16px;
            color: $base-700;
        }

        &__content {
            display: flex;
            min-height: 216px;
            border-radius: $min-border-radius;
            border: 1px solid $border-color;
        }

        &__prod-rat {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-content: center;
            width: 236px;
            text-align: center;
        }

        &__req {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 24px;
            border-left: 1px solid $border-color;
        }
    }

    .rat-badge {
        display: flex;
        align-items: center;
        width: 227px;
        min-height: 43px;
        padding: 0 12px;
        border-radius: $min-border-radius;
        background: $base-100;

        &.success {
            border: 1px solid $success;
        }

        &.alert {
            border: 1px solid $error;
        }

        &__text {
            flex: auto;
            font-size: 12px;
        }

        &__grade {
            margin: 0 12px;
            font-size: 20px;
            font-weight: bold;
        }
    }

    .prod-rat {
        &__grade {
            font-size: 40px;
            font-weight: bold;
            line-height: 1;
        }

        &__reviews {
            font-weight: bold;
            font-size: 12px;
            color: $base-700;
        }
    }
</style>
