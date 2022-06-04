<template>
    <div class="feetback-block">
        <div class="feetback-block__item">
            <div class="feetback-block__item-title">
                <div class="feetback-block__item-avatar">
                    <img src="/images/EVER-1.jpg" />
                </div>
                <div class="feetback-block__item-name">
                    Алексей,
                    <br />
                    руководитель бренда Декун
                </div>
            </div>
            <div class="feetback-block__item-text">
                SellerExpert, вы — молодцы, что делаете такие сложные и важные вещи доступными
                частным предпринимателям
            </div>
        </div>

        <div class="feetback-block__item">
            <div class="feetback-block__item-title">
                <div class="feetback-block__item-avatar">
                    <img src="/images/EVER-3.jpg" />
                </div>
                <div class="feetback-block__item-name">
                    Ирина,
                    <br />
                    маркетолог ООО Монблан
                </div>
            </div>
            <div class="feetback-block__item-text">
                Мне кажется, это единственное решение по оптимизации карточек товара с таким
                детальным функционалом
            </div>
        </div>

        <div class="feetback-block__item">
            <div class="feetback-block__item-title">
                <div class="feetback-block__item-avatar">
                    <img src="/images/EVER-2.jpg" />
                </div>
                <div class="feetback-block__item-name">
                    Елена,
                    <br />
                    менеджер бренда Kёrk
                </div>
            </div>
            <div class="feetback-block__item-text">
                Классный инструмент для Ozon. Радует, что ребята планируют серьезное развитие в этом
                направлении. Жду с нетерпением
            </div>
        </div>
    </div>
</template>
<script>
    export default {};
</script>
<style lang="scss" scoped>
    .feetback-block {
        position: absolute;
        top: 120px;
        right: 10%;
        z-index: 9;

        @media screen and (max-width: 1024px) {
            display: none;
        }

        &__item {
            position: relative;
            width: 410px;
            margin-bottom: 53px;
            padding: 20px;
            border-radius: 20px;
            border: 1px solid #7e21ff;
            background: #fff;

            &:nth-child(2) {
                right: 44px;
            }
        }

        &__item-title {
            display: flex;
            align-items: center;
        }

        &__item-avatar {
            overflow: hidden;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 3px solid #cba8fb;

            & img {
                width: 100%;
            }
        }

        &__item-name {
            padding-left: 10px;
            font-size: 12px;
            font-style: normal;
            font-weight: normal;
            line-height: 15px;
            color: #710bff;
        }

        &__item-text {
            padding-top: 10px;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 17px;
            color: #1e2024;
        }
    }
</style>
