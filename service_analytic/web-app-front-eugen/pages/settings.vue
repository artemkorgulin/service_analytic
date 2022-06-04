<template>
    <div :class="$style.SettingsPage">
        <Page :title="title">
            <VSheet :class="$style.sheet">
                <h2 :class="$style.blockHeading">Мои данные</h2>
                <div class="fields-grid">
                    <VTextField
                        v-model="form.name.$model.value"
                        :error-messages="form.name.$errorMessage.value"
                        autocomplete="new-password"
                        label="ФИО"
                        class="light-outline"
                        dense
                        outlined
                        hide-details
                        @blur="form.name.$touch"
                        @input="form.name.$resetExtError"
                    />
                    <VTextField
                        v-model="form.email.$model.value"
                        :error-messages="form.email.$errorMessage.value"
                        autocomplete="new-password"
                        type="email"
                        label="Email"
                        class="light-outline"
                        hide-details
                        dense
                        outlined
                        @blur="form.email.$touch"
                        @input="form.email.$resetExtError"
                    />
                    <VTextField
                        v-model="form.phone.$model.value"
                        v-mask="'+7 (###) ###-##-##'"
                        autocomplete="new-password"
                        label="Номер телефона"
                        class="light-outline"
                        dense
                        outlined
                        hide-details
                        @blur="form.phone.$touch"
                        @input="form.phone.$resetExtError"
                    />
                    <VTextField
                        v-model="form.password.$model.value"
                        class="fields-grid__left light-outline"
                        :error-messages="form.password.$errorMessage.value"
                        label="Пароль"
                        autocomplete="new-password"
                        :type="!isShowPass ? 'password' : 'text'"
                        :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                        outlined
                        hide-details
                        dense
                        @blur="form.password.$touch"
                        @input="form.password.$resetExtError"
                        @click:append="handleToggleShowPass"
                    />
                    <VTextField
                        v-model="form.password1.$model.value"
                        :error-messages="form.password1.$errorMessage.value"
                        label="Повторите пароль"
                        autocomplete="new-password"
                        :type="!isShowPass ? 'password' : 'text'"
                        :append-icon="!isShowPass ? '$eye' : '$eyeOff'"
                        outlined
                        class="light-outline"
                        dense
                        @blur="form.password1.$touch"
                        @input="form.password1.$resetExtError"
                        @click:append="handleToggleShowPass"
                    />
                </div>
                <v-expand-transition>
                    <ul
                        v-if="form.password.$model.value"
                        class="pass-valid-list fields-grid__valid-list"
                    >
                        <li
                            v-for="item in validPasswordList"
                            :key="item.text"
                            class="pass-valid-list__item"
                            :class="{ active: item.validate }"
                        >
                            <SvgIcon
                                name="filled/check"
                                class="mr-2"
                                :color="item.validate ? '#710bff' : ''"
                            ></SvgIcon>
                            {{ item.text }}
                        </li>
                    </ul>
                </v-expand-transition>
                <VBtn
                    color="accent"
                    :class="$style.saveBtn"
                    depressed
                    class="se-btn"
                    :loading="loading"
                    @click="saveChanges"
                >
                    Сохранить
                </VBtn>
            </VSheet>

            <VSheet :class="$style.sheet">
                <h2 :class="$style.blockHeading">Получать уведомления</h2>
                <div class="settings-page__buttons-messages">
                    <VBtn
                        depressed
                        class="
                            settings-page__button-messages settings-page__button-messages--telegram
                        "
                        @click="openTelegram"
                    >
                        <span>Подключить</span>
                        <svg
                            width="25"
                            height="20"
                            viewBox="0 0 25 20"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M23.8053 0.259648C23.9821 0.249534 24.1587 0.281331 24.3207 0.352439C24.4828 0.423547 24.6257 0.531943 24.7377 0.668758C24.8497 0.805573 24.9277 0.966918 24.9652 1.13959C25.0028 1.31226 24.9988 1.49135 24.9537 1.66221C23.6724 7.32871 22.3897 12.9945 21.1057 18.6596C20.8739 19.6798 19.9437 20.0507 19.071 19.4641C17.2364 18.2289 15.4063 16.9861 13.5808 15.7357C13.3093 15.5492 13.1632 15.569 12.9387 15.792C12.1035 16.6121 11.2433 17.4092 10.3894 18.2105C9.63353 18.9201 8.82968 18.7211 8.49665 17.7437C7.84522 15.8378 7.19065 13.9341 6.55279 12.024C6.45675 11.7365 6.32103 11.5812 6.01515 11.503C4.25607 11.0393 2.50325 10.5517 0.749394 10.0723C0.259775 9.93897 0.0269708 9.71389 0.00191565 9.34293C-0.0231395 8.97197 0.197137 8.6948 0.688844 8.51765L23.1998 0.401362C23.3934 0.324127 23.5975 0.276356 23.8053 0.259648ZM7.40362 11.4801C7.41615 11.5228 7.44329 11.6364 7.47983 11.7458C7.98789 13.2741 8.49839 14.8024 9.01133 16.3307C9.04891 16.4422 9.03534 16.6433 9.20341 16.6298C9.37149 16.6162 9.32869 16.4214 9.34226 16.3099C9.46232 15.3721 9.58237 14.4269 9.67529 13.4829C9.68351 13.3288 9.7251 13.1783 9.79719 13.0417C9.86928 12.9052 9.97015 12.7859 10.0929 12.692C12.8482 10.4516 15.5973 8.20574 18.3402 5.95428C19.0362 5.38673 19.7321 4.81814 20.4281 4.2485C20.5482 4.14951 20.6912 4.04009 20.5753 3.85982C20.4594 3.67956 20.2924 3.71186 20.1306 3.80147C20.0732 3.83273 20.0168 3.86608 19.9604 3.89838L13.7697 7.44125C11.7855 8.57844 9.80196 9.71563 7.81912 10.8528C7.59258 10.9799 7.38483 11.1081 7.40362 11.4801Z"
                                fill="#FDFDFE"
                            />
                        </svg>
                    </VBtn>
                    <VBtn
                        class="
                            settings-page__button-messages settings-page__button-messages--whatsapp
                        "
                        disabled
                    >
                        <span>Подключить</span>
                        <svg
                            width="26"
                            height="25"
                            viewBox="0 0 26 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M25.7883 12.2472C25.8175 18.4036 21.4042 23.6193 15.4408 24.6101C12.9751 25.0353 10.4381 24.6934 8.17368 23.6308C7.97945 23.5347 7.75683 23.5125 7.54739 23.5683C5.64136 24.0288 3.7322 24.4747 1.82304 24.9227C1.18317 25.0727 0.938915 24.83 1.07252 24.1798C1.47266 22.2461 1.87453 20.3127 2.27815 18.3797C2.33125 18.1528 2.30396 17.9146 2.2009 17.7056C-1.37525 10.1728 3.33867 1.36572 11.5954 0.135253C18.3019 -0.863913 24.6213 3.78185 25.638 10.4666C25.7252 11.0564 25.7754 11.6511 25.7883 12.2472ZM13.4095 2.6837C12.746 2.69226 12.0845 2.7585 11.4325 2.88166C7.12046 3.71517 3.81988 7.62536 3.6894 12.0148C3.63201 13.8433 4.07556 15.6527 4.97227 17.2482C5.11625 17.4934 5.15751 17.7854 5.08709 18.0609C4.85849 19.1027 4.65599 20.1446 4.42634 21.1865C4.35954 21.4866 4.43261 21.5428 4.71862 21.472C5.71443 21.2261 6.71754 21.0104 7.71022 20.7531C7.86933 20.7057 8.03643 20.6912 8.20135 20.7103C8.36627 20.7295 8.52555 20.782 8.6695 20.8646C10.444 21.8148 12.3229 22.2315 14.3458 22.0388C18.97 21.597 22.6965 17.8473 23.0639 13.2265C23.5211 7.53471 19.0973 2.70454 13.4095 2.6837Z"
                                fill="#FCFDFC"
                            />
                            <path
                                d="M16.6209 18.4254C15.2274 18.3963 13.9748 17.9264 12.8172 17.1825C10.7484 15.8436 8.96342 14.2121 7.79329 12.0022C7.27137 11.0083 6.9791 9.93512 7.21396 8.80259C7.42273 7.80551 8.09182 7.10328 8.89139 6.5365C9.29849 6.2479 9.86424 6.43231 10.0866 6.89178C10.4634 7.67527 10.8047 8.47648 11.1492 9.27561C11.2079 9.41458 11.2278 9.56687 11.2067 9.71622C11.1855 9.86558 11.1242 10.0064 11.0292 10.1237C10.8841 10.3237 10.7275 10.5155 10.5793 10.7134C10.2431 11.1635 10.2264 11.4948 10.5553 11.9637C11.2317 12.9191 12.1095 13.6765 13.0051 14.4111C13.4305 14.7724 13.8959 15.0839 14.3924 15.3394C14.8757 15.578 15.2546 15.5603 15.5834 15.1372C16.1794 14.3715 16.861 14.2266 17.7389 14.5944C18.2378 14.8028 18.7827 14.8976 19.3046 15.057C20.0165 15.2758 20.2514 15.9082 19.8338 16.5271C19.289 17.3346 18.6794 18.0795 17.6606 18.3129C17.3204 18.3952 16.9709 18.433 16.6209 18.4254Z"
                                fill="#FCFDFC"
                            />
                        </svg>
                    </VBtn>
                    <VBtn
                        disabled
                        class="settings-page__button-messages settings-page__button-messages--viber"
                    >
                        <span>Подключить</span>
                        <svg
                            width="25"
                            height="25"
                            viewBox="0 0 25 25"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M13.1982 21.8672C12.6125 21.8672 12.0279 21.8776 11.4423 21.862C11.347 21.854 11.2513 21.8688 11.1629 21.905C11.0745 21.9413 10.996 21.9979 10.9338 22.0704C10.1655 22.8832 9.38043 23.6803 8.61103 24.491C8.24877 24.8745 7.84058 25.0651 7.31964 24.8713C6.87596 24.7056 6.64942 24.3055 6.64733 23.7063C6.64733 23.0051 6.63585 22.3027 6.65255 21.6015C6.65882 21.3545 6.57947 21.2451 6.33936 21.1555C4.16061 20.3219 2.58109 18.8297 1.60394 16.7186C1.14668 15.7141 1.0663 14.6085 0.937892 13.5289C0.651666 11.0594 0.776182 8.55963 1.30641 6.13061C1.56114 4.95104 2.18334 3.97258 2.97675 3.0952C4.04995 1.9073 5.29957 0.939266 6.89371 0.60061C9.39459 0.0842282 11.9528 -0.100415 14.5021 0.0514651C15.8792 0.123788 17.25 0.285645 18.6059 0.536004C19.9579 0.785047 21.0707 1.4832 22.0719 2.37725C23.0438 3.24525 23.8404 4.25289 24.2757 5.49497C24.5993 6.4182 24.709 7.39249 24.8186 8.35844C25.0986 10.8146 24.977 13.2999 24.4584 15.7172C24.1358 17.2312 23.2568 18.3973 22.1732 19.4414C20.8077 20.7595 19.1572 21.4212 17.2811 21.6119C15.923 21.7536 14.5648 21.8859 13.1982 21.8672ZM7.54305 21.5546C7.54305 22.1913 7.54305 22.8269 7.54305 23.4636C7.54305 23.6345 7.47102 23.8564 7.69547 23.9418C7.91992 24.0273 8.02119 23.8272 8.14333 23.7022C9.35224 22.467 10.5591 21.2291 11.7638 19.9884C11.8334 19.9053 11.9214 19.8393 12.0208 19.7957C12.1203 19.7522 12.2285 19.7322 12.3369 19.7373C13.954 19.779 15.5576 19.6331 17.1611 19.4351C18.3648 19.2851 19.4672 18.9349 20.3901 18.117C21.339 17.276 22.1481 16.3518 22.3987 15.0513C22.9206 12.3681 22.8726 9.69744 22.4436 6.99757C22.1398 5.08754 19.7491 2.89514 18.0766 2.62109C14.9808 2.09643 11.8233 2.04233 8.71125 2.46061C7.59212 2.61379 6.51266 2.85033 5.60963 3.59746C4.58133 4.4467 3.66786 5.37514 3.38286 6.74123C2.86087 9.24729 2.84626 11.7617 3.20851 14.2938C3.56451 16.7821 5.02919 18.3129 7.29041 19.1913C7.51695 19.2799 7.54201 19.4122 7.54096 19.6081C7.54096 20.2552 7.54305 20.9054 7.54305 21.5546Z"
                                fill="#FCFCFD"
                            />
                            <path
                                d="M6.15918 7.74643C6.18512 7.87854 6.22 8.00874 6.26358 8.13614C6.55797 8.88431 6.87847 9.62102 7.24281 10.34C8.99459 13.8006 11.8415 15.993 15.3972 17.3726C15.8858 17.5633 16.3086 17.4768 16.7251 17.208C17.2213 16.8916 17.6239 16.4488 17.8912 15.9252C18.1794 15.373 18.1157 15.0604 17.6365 14.6654C17.0702 14.2085 16.4799 13.7821 15.868 13.3879C15.131 12.9034 14.7197 12.9711 14.1747 13.6766C13.7415 14.2288 13.444 14.3018 12.7873 14.0277C11.4554 13.4937 10.3846 12.4624 9.80261 11.1528C9.34953 10.1816 9.28794 9.92946 10.1795 9.27715C10.62 8.95517 10.6952 8.46646 10.4311 7.98922C9.99403 7.20636 9.46863 6.47601 8.86513 5.81244C8.75794 5.67869 8.6078 5.58596 8.44005 5.54989C8.2723 5.51382 8.09722 5.53663 7.94436 5.61446C7.30527 5.88639 6.76701 6.35043 6.40451 6.94199C6.25606 7.18519 6.17167 7.46192 6.15918 7.74643Z"
                                fill="#FDFCFD"
                            />
                            <path
                                d="M12.5555 4.8516C12.5273 4.8516 12.424 4.8516 12.3196 4.8516C12.2737 4.84803 12.2275 4.85392 12.1839 4.86891C12.1403 4.8839 12.1003 4.90767 12.0664 4.93875C12.0324 4.96982 12.0052 5.00754 11.9864 5.04955C11.9677 5.09156 11.9578 5.13697 11.9573 5.18296C11.9406 5.39137 12.0889 5.48098 12.2705 5.51224C12.4229 5.53829 12.5785 5.55184 12.7341 5.5633C15.2865 5.75295 17.2085 7.43372 17.6553 9.89081C17.7357 10.3347 17.7378 10.7932 17.791 11.2454C17.8161 11.4601 17.8881 11.6706 18.1679 11.6456C18.4477 11.6206 18.4967 11.4288 18.4811 11.1965C18.4571 10.8995 18.4299 10.6025 18.4038 10.3045C18.271 8.83697 17.6012 7.46971 16.5224 6.46386C15.4436 5.45801 14.0314 4.88405 12.5555 4.8516Z"
                                fill="#FDFCFD"
                            />
                            <path
                                d="M16.9113 10.3192C16.881 9.72053 16.7245 9.13501 16.4519 8.60089C15.7013 7.18791 14.4684 6.54082 12.916 6.41265C12.6926 6.39389 12.5162 6.46058 12.4869 6.69921C12.4577 6.93783 12.6122 7.05037 12.8325 7.08475C12.9369 7.10038 13.0413 7.10976 13.1457 7.12227C14.7837 7.31608 15.9258 8.38832 16.1888 9.98782C16.2285 10.2296 16.216 10.4797 16.2473 10.7172C16.2482 10.7598 16.2577 10.8017 16.2754 10.8404C16.293 10.8791 16.3184 10.9138 16.35 10.9424C16.3816 10.971 16.4187 10.9928 16.459 11.0066C16.4993 11.0204 16.542 11.0258 16.5845 11.0225C16.7933 11.0163 16.8841 10.885 16.9134 10.7016C16.92 10.5742 16.9193 10.4465 16.9113 10.3192Z"
                                fill="#FDFCFD"
                            />
                            <path
                                d="M15.365 10.0694C15.3212 8.9138 14.5789 8.13436 13.4734 8.04267C13.2228 8.02183 12.9754 8.02078 12.943 8.33756C12.9128 8.63557 13.1518 8.6981 13.3825 8.71685C13.987 8.76583 14.4119 9.043 14.5956 9.63487C14.6402 9.79653 14.6727 9.96128 14.6927 10.1277C14.7282 10.3612 14.842 10.5352 15.0936 10.5102C15.3713 10.4831 15.3807 10.258 15.365 10.0694Z"
                                fill="#FDFCFD"
                            />
                        </svg>
                    </VBtn>
                </div>
            </VSheet>
            <div :class="$style.ctaWrapper">
                <div :class="$style.ctaTitle">Остались вопросы?</div>
                <div :class="$style.ctaSubTitle">
                    Напишите нам — мы постараемся ответить в течение 24 часов.
                </div>
                <VBtn outlined @click="handleOpenModalSupportCTA">Написать нам</VBtn>
            </div>
        </Page>
    </div>
</template>

<script>
    /* eslint-disable no-empty-function */
    import { mapState, mapGetters } from 'vuex';
    import { defineComponent, useContext, reactive } from '@nuxtjs/composition-api';
    import { mask } from 'vue-the-mask';
    import { minLength, required, email, minLengthIfAny } from '~utils/patterns';
    import { useForm } from '~use/form';
    import { useField } from '~use/field';
    import Page from '~/components/ui/SeInnerPage';

    export default defineComponent({
        name: 'SettingsPage',
        directives: { mask },
        components: {
            Page,
        },

        transition: {
            name: 'fade',
            mode: 'out-in',
        },
        setup() {
            const { store } = useContext();
            const nameUser = store.state.auth.user.name;
            const emailUser = store.state.auth.user.email;
            const phoneUser = store.state.auth.user.phone;
            const formFields = {
                email: {
                    value: emailUser,
                    validators: { required, email },
                    errorMessages: {
                        required: 'Введите email',
                        email: 'Введите корректный email',
                    },
                },
                name: {
                    value: nameUser,
                    validators: { required, minLength: minLength(2) },
                    errorMessages: {
                        required: 'Введите имя',
                        minLength: 'Слишком короткое имя',
                    },
                },
                phone: {
                    value: phoneUser,
                    validators: { minLength: minLengthIfAny(1) },
                    errorMessages: {
                        minLength: 'Слишком короткий номер',
                        phone: 'Введите корректный номер',
                    },
                },
                password: {
                    validators: {
                        minLength: minLengthIfAny(8),
                        passwordRules1: value =>
                            !value || /^(?=.*[a-zA-Z])(?=.*[0-9]).{8,}/.test(value),
                        passwordRules2: value => !value || /(?=.*[A-Z])/.test(value),
                    },
                    errorMessages: {
                        minLength: 'Минимальная длина пароля 8 символов',
                    },
                },
                password1: {
                    validators: {
                        sameAs(val, field, formInstance) {
                            const pass = formInstance.password.$model.value;
                            return pass === val;
                        },
                    },
                    errorMessages: {
                        minLength: 'Минимальная длина пароля 8 символов',
                        sameAs: 'Пароли должны совпадать',
                    },
                },
            };
            const formObject = reactive({});
            for (const field in formFields) {
                formObject[field] = useField(formFields[field], formObject);
            }
            const $validation = useForm(formObject);
            return {
                ...$validation,
            };
        },
        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Пользователь',
                },
                isMounted: false,
                isShowPass: false,
                loading: false,
                selectedCompany: null,
                isNotificationsEnabled: this.$auth?.$state?.user?.enable_email_notifications,
                notification: () => {},
                mpExpanded: null,
            };
        },
        head() {
            return {
                title: 'Настройки',
                htmlAttrs: {
                    class: 'static-rem',
                },
            };
        },
        computed: {
            ...mapState('user', {
                companies: 'companies',
            }),
            ...mapGetters({
                marketplaces: 'getMarketplacesItems',
            }),
            isNotificationsEnabledInitial() {
                return Boolean(this.$auth?.$state?.user?.enable_email_notifications);
            },
            isChanges() {
                return this.$changed;
            },
            validPasswordList() {
                const { value = '' } = this.form.password.$model;
                return [
                    { text: 'Минимум - 8 символов', validate: value.length > 7 },
                    {
                        text: 'Содержит цифры и латинские буквы',
                        validate: /^(?=.*[a-zA-Z])(?=.*[0-9]).{8,}/.test(value),
                    },
                    {
                        text: 'Содержит заглавные и строчные буквы',
                        validate: /(?=.*[A-Z])/.test(value),
                    },
                ];
            },
        },
        mounted() {
            this.isMounted = true;
        },
        methods: {
            async openTelegram() {
                const { data } = await this.$axios.$get('/api/event/v1/telegram-bot-start-link');
                window.open(data.url, '_blank');
            },
            handleToggleShowPass() {
                this.isShowPass = !this.isShowPass;
            },
            handleExpand(payload) {
                this.mpExpanded = this.mpExpanded === payload ? null : payload;
            },
            async saveChanges() {
                try {
                    this.$touch();
                    if (this.$invalid) {
                        return;
                    }
                    this.notification();
                    const mailing = {
                        enable_email_notifications: this.isNotificationsEnabled,
                    };
                    const personalData = {
                        name: this.form.name.$model.value,
                        email: this.form.email.$model.value,
                        phone: this.form.phone.$model.value,
                        password: this.form.password.$model.value,
                    };
                    this.loading = true;
                    await this.$axios.$post('/api/v1/set-settings', {
                        ...mailing,
                        ...personalData,
                    });
                    await this.$nuxt.$auth.fetchUser();
                    this.notification = this.$notify.create({
                        message: 'Настройки успешно изменены',
                        type: 'positive',
                    });
                    this.loading = false;
                } catch (error) {
                    if (error?.response?.data?.errors?.email) {
                        const errorsAdvanced =
                            Object.values(error.response.data.errors).flat() || [];
                        errorsAdvanced.forEach(err => {
                            this.notification = this.$notify.create({
                                message: err,
                                type: 'negative',
                            });
                        });
                    } else {
                        this.notification = this.$notify.create({
                            message:
                                error?.response?.error?.message ||
                                error?.message ||
                                'Произошла ошибка',
                            type: 'negative',
                        });
                    }

                    this.loading = false;
                }
            },
            handleOpenModalSupportCTA() {
                return this.$modal.open({
                    component: 'ModalSupportCTA',
                });
            },
        },
    });
</script>
<style lang="scss">
    .settings-page {
        &__buttons-messages {
            display: flex;
            padding-top: 8px;
            gap: 16px;

            @media screen and (max-width: 600px) {
                flex-direction: column;
            }
        }

        &__button-messages {
            &.v-btn.v-btn--has-bg {
                background-color: #710bff;
                color: #fff;
            }

            & span {
                margin-right: 10px;
            }

            &.v-btn--disabled.v-btn.v-btn--has-bg {
                background-color: #c8cfd9 !important;
                color: #fff !important;
            }

            &--telegram.v-btn.v-btn--has-bg {
                background-color: #1c8ada;
            }

            &--whatsapp.v-btn.v-btn--has-bg {
                background-color: #29a61a;
            }

            &--viber.v-btn.v-btn--has-bg {
                background-color: #6f3fa9;
            }
        }
    }
</style>
<style lang="scss" module>
    /* stylelint-disable declaration-no-important */

    .mp {
        .mpList {
            display: flex;
            flex-direction: column;
        }

        .blockHeading {
            margin-bottom: 0;
            padding: 16px;
        }
    }

    .SettingsPage {
        position: relative;
        min-height: 100%;

        & :global(.v-sheet) {
            @include cardShadow;
        }
    }

    .tabs {
        position: relative;
        width: unset;
        border-bottom: thin solid $base-400;
    }

    .tab {
        text-decoration: none !important;
        text-transform: none !important;
        font-size: 14px !important;
        letter-spacing: unset !important;
        font-weight: 500 !important;
    }

    .inputsWrapper {
        display: flex;
        gap: 16px;
    }

    .blockHeading {
        margin-bottom: 8px;
        font-size: 24px;
        line-height: 32px;
        color: $base-900;
        font-weight: 500;
    }

    .heading {
        margin-bottom: 16px;
        font-size: 26px;
        font-weight: 600;
    }

    .sheet {
        @extend %sheet;

        max-width: 1048px;
        margin-bottom: 16px;
        padding: 16px;
        gap: 16px;

        @include respond-to(sm) {
            flex-direction: column;
        }

        &.subscriptionWrapper {
            padding-top: 16px;
            padding-bottom: 16px;
        }

        &.mp {
            padding: 0;
        }
    }

    .input {
        flex-basis: 325px;

        @include respond-to(lg) {
            flex-basis: 50%;
            max-width: 50%;
        }

        @include respond-to(sm) {
            flex-basis: 100%;
            max-width: 100%;
        }
    }

    .marketplacesWrapper {
        max-width: 1048px;
        margin-bottom: 32px;
        border-radius: 24px;
    }

    .marketplaceInner {
        display: flex;
        gap: 32px;
    }

    .marketplaceHeadingWrapper {
        padding-top: 20px;
        padding-bottom: 18px;
    }

    .marketplaceHeading {
        @extend %text-h5;

        margin-left: 16px;
    }

    .saveBtn {
        margin-top: 16px;
    }

    .ctaWrapper {
        margin-top: auto;

        @include md {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            flex-direction: column;
            text-align: center;
        }
    }

    .ctaTitle {
        @extend %text-h4;

        margin-bottom: 8px;
        font-size: 20px;
        color: $base-900;
        font-weight: bold;
    }

    .ctaSubTitle {
        @extend %text-body-1;

        margin-bottom: 16px;
        color: $base-800;
    }
</style>

<style lang="scss" scoped>
    .pass-valid-list {
        flex-grow: 2;
        margin: 0;
        padding: 0;
        text-align: left;

        &__item {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 12px;
            color: $error;

            &.active {
                color: $primary-500;
            }
        }
    }

    .fields-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;

        @media screen and (max-width: 600px) {
            grid-template-columns: repeat(1, 1fr);
        }
    }
</style>
