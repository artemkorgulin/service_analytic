<template>
    <div class="reg-users-counter reg-users-counter_place_tl">
        <div class="reg-users-counter__icon">
            <img src="~/assets/sprite/svg/common/people.svg" alt="icon-people" />
        </div>
        <div class="reg-users-counter__count">{{ count }}</div>
        <div class="reg-users-counter__text">Продавцов уже увеличивают продажи в нашем сервисе</div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                count: undefined,
            };
        },
        async mounted() {
            await this.getNumOfUsers();
        },
        methods: {
            async getNumOfUsers() {
                try {
                    const topic = '/api/inner/lending-users-count';
                    const key = process.env.REG_USER_COUNTER_TOKEN;
                    const { data } = await this.$axios.get(topic, {
                        headers: { 'Authorization-Web-App': key },
                    });

                    this.count = data;
                } catch (error) {
                    console.error(error);
                }
            },
        },
    };
</script>
<style lang="scss" scoped>
    .reg-users-counter {
        max-width: 360px;
        margin: auto;
        padding-top: 36px;
        padding-bottom: 124px;
        border-top: 1px solid $border-color;
        text-align: center;

        @media screen and (min-width: map-get($breakpoints, 'md')) {
            padding-top: 0;
            padding-bottom: 0;
            border-top: none;
        }

        &_place_tl {
            @media screen and (min-width: map-get($breakpoints, 'md')) {
                position: absolute;
                top: 150px;
                left: 40%;
                z-index: 9;
            }
        }

        &__count {
            font-weight: bold;
            font-size: 56px;
            color: $primary-color;

            @media screen and (min-width: map-get($breakpoints, 'md')) {
                font-size: 80px;
            }
        }

        &__text {
            font-size: 24px;
            font-weight: bold;
            line-height: 1;

            @media screen and (min-width: map-get($breakpoints, 'md')) {
                font-size: 32px;
            }
        }
    }
</style>
