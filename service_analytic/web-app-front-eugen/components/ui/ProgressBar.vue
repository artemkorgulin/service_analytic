<template>
    <div class="progress-bar">
        <div class="progress-bar__monitor-parametr-name">
            {{ title }}
        </div>
        <div
            class="progress-bar__monitor-parametr-progress"
            :class="progressbarClass"
        >
            <v-scroll-y-transition>
                <div v-if="isLoading" class="progress-bar__loading-desc">
                    Идет расчет. Точные данные отобразятся через 24 часа после выгрузки на
                    {{ isSelectedMp.name }}
                </div>
            </v-scroll-y-transition>
            <div
                v-if="isLoading"
                class="progress-bar__loading"
                :class="`progress-bar__loading--${color}`"
            ></div>
            <div class="progress-bar__monitor-max">{{ max }}{{ prefix }}</div>
            <div class="progress-bar__monitor-value" :style="`width: ${progress}%`">
                {{ thisValue }}{{ prefix }}
                <div
                    class="progress-bar__monitor-pointer"
                    :class="{
                        'progress-bar__monitor-pointer--left': progress < 20,
                    }"
                >
                    {{ desc }}
                </div>
            </div>
        </div>
        <div v-if="progress < 85" class="progress-bar__monitor-parametr-desc">Максимум</div>
    </div>
</template>
<script>
    import { mapGetters } from 'vuex';
    export default {
        props: {
            isLoading: Boolean,
            title: String,
            desc: String,
            prefix: String,
            value: {},
            max: {},
            color: {
                type: String,
                validator(value) {
                    return ['green', 'violet'].indexOf(value) !== -1;
                },
            },
        },
        computed: {
            ...mapGetters(['isSelectedMp']),
            progress() {
                return Math.round(100 / (this.max / this.value));
            },
            thisValue() {
                return Math.round(this.value);
            },
            progressbarClass() {
                return this.value ? `progress-bar__monitor-parametr-progress--${this.color}` : 'progress-bar__monitor-parametr-progress--red';
            },
        },
    };
</script>
<style lang="scss" scoped>
    // TODO: Игорь, Необходимо все значения переменных увести в переменные и миксины, такие как цвета и размеры шрифтов и.т.п
    .progress-bar {
        padding-bottom: 40px;

        &__loading {
            position: absolute;
            height: 100%;
            border-radius: 8px 0 0 8px;
            animation: loader 8s ease infinite;

            &--green {
                background: #cbf4de;
            }

            &--violet {
                background: #ece0fa;
            }
        }

        &__loading-desc {
            position: absolute;
            top: -18px;
            left: 0;
            font-size: 10px;
            color: #7e8793;
        }

        .progress-bar__monitor-parametr-name {
            padding-bottom: 20px;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 19px;
            color: #2f3640;
        }

        .progress-bar__monitor-parametr-progress {
            position: relative;
            height: 40px;
            border-radius: 8px;
            border: 1px solid #e9edf2;

            .progress-bar__monitor-max {
                position: absolute;
                top: 0;
                right: 12px;
                font-size: 20px;
                font-style: normal;
                font-weight: bold;
                line-height: 40px;
                color: #e9edf2;
            }

            .progress-bar__monitor-value {
                position: absolute;
                top: 0;
                left: 0;
                min-width: 55px;
                height: 38px;
                padding-right: 12px;
                border-radius: 8px 0 0 8px;
                background: #e9edf2;
                text-align: right;
                font-size: 20px;
                font-style: normal;
                font-weight: bold;
                line-height: 40px;
                color: #fff;

                &--full {
                    border-radius: 8px;
                }

                .progress-bar__monitor-pointer {
                    position: absolute;
                    top: 40px;
                    right: 0;
                    width: 110px;
                    height: 40px;
                    padding: 5px 10px 0 0;
                    border-right: 1px solid #e9edf2;
                    text-align: right;
                    font-size: 12px;
                    font-style: normal;
                    font-weight: normal;
                    line-height: 16px;
                    color: #7e8793;

                    &--left {
                        right: -110px;
                        padding: 5px 0 0 10px;
                        border-right: none;
                        border-left: 1px solid #e9edf2;
                        text-align: left;
                    }
                }
            }

            &--green .progress-bar__monitor-value {
                background: #5ddc98;
            }

            &--violet .progress-bar__monitor-value {
                background: #a46fe8;
            }

            &--red .progress-bar__monitor-value {
                background: rgb(245, 96, 148);
            }
        }

        .progress-bar__monitor-parametr-desc {
            text-align: right;
            font-size: 12px;
            font-style: normal;
            font-weight: normal;
            line-height: 16px;
            color: #7e8793;
        }
    }

    @keyframes loader {
        0% {
            width: 0;
        }

        25% {
            width: 25%;
        }

        50% {
            width: 50%;
        }

        75% {
            width: 75%;
        }

        100% {
            width: 100%;
            border-radius: 8px;
        }
    }
</style>
