<template>
    <div class="depon-images">
        <h2 class="depon-images__title">Защита авторства</h2>
        <div class="depon-images__descript">
            При обнаружении копирования ваших изображений вы сможете направить претензию в
            {{ isSelectedMp.name }}, только если ваша карточка депонирована. Если вы обновили часть
            изображений, карточку нужно депонировать заново.
        </div>

        <div ref="deponImagesInfo" class="depon-images__info">
            <div class="depon-images__data-tovar">
                <div class="depon-images__data-tovar-contener">
                    <div class="depon-images__data-tovar-img">
                        <img
                            v-if="isSelectedMp.id === 2"
                            :src="options[getActiveOptionIndex].image"
                        />
                        <img v-else :src="info.photo" />
                    </div>

                    <div class="depon-images__data-tovar-text">
                        <div class="depon-images__data-tovar-item-text">
                            <a
                                v-if="isSelectedMp.id === 2"
                                :href="`https://www.wildberries.ru/catalog/${options[getActiveOptionIndex].nmId}/detail.aspx`"
                                target="_blank"
                                class="product-preview__sku"
                            >
                                {{ options[getActiveOptionIndex].nmId }}
                                <SvgIcon
                                    name="outlined/link"
                                    class="product-preview__sku-icon"
                                    data-right
                                />
                            </a>
                            <div v-else>
                                <div
                                    v-for="(el, i) in ozonData"
                                    :key="i"
                                    class="depon-images__fb-element"
                                >
                                    <a :href="el.url" target="_blank" class="product-preview__sku">
                                        {{ el.id }}
                                        <SvgIcon
                                            name="outlined/link"
                                            class="product-preview__sku-icon"
                                        />
                                    </a>
                                </div>
                            </div>

                            <div class="depon-images__info-additional">{{ info.name }}</div>
                        </div>

                        <div class="depon-images__data-tovar-item-text">
                            <div class="depon-images__data-tovar-item-title">
                                Статус депонирования
                            </div>
                            <div class="depon-images__data-tovar-item-value">
                                <span v-if="certificates.length > 0">Депонировано</span>
                                <span v-else>Не депонировано</span>
                            </div>
                        </div>

                        <div class="depon-images__data-tovar-item-text">
                            <div class="depon-images__data-tovar-item-title">
                                Актуальность сертификата
                            </div>
                            <div class="depon-images__data-tovar-item-value">
                                <div v-if="certificates.length > 0">
                                    <span>{{ formatDate(certificates[0].created_at) }}</span>
                                    <a
                                        class="depon-images__link-certificates"
                                        target="_blank"
                                        :href="certificates[0].link"
                                    >
                                        Посмотреть
                                    </a>
                                </div>
                                <div v-else>Сертификат отсутствует</div>
                            </div>
                        </div>

                        <div class="depon-images__data-tovar-item-text">
                            <div class="depon-images__data-tovar-item-title">
                                Осталось депонирований
                            </div>
                            <div class="depon-images__data-tovar-item-value">
                                {{ escrow.remainLimit }} из {{ escrow.totalLimit }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="depon-images__form-title">Заполните поля для депонирования</div>
                <v-form ref="form" v-model="valid" lazy-validation>
                    <VTextField
                        v-model="form.name"
                        :label="'Наименование правообладателя'"
                        class="light-outline input-required"
                        outlined
                        dense
                        :rules="[
                            val => Boolean(val) || 'Укажите значение',
                            val =>
                                val.length > 3 ||
                                'Поле ввода должно содержать не менее 3-х символов',
                        ]"
                        color="$color-purple-primary"
                    />
                    <VTextField
                        v-model="form.fio"
                        label="ФИО"
                        class="light-outline input-required"
                        outlined
                        dense
                        color="$color-purple-primary"
                        :rules="[
                            val => Boolean(val) || 'Укажите значение',
                            val =>
                                val.length < 50 ||
                                'Количество символов в поле ФИО не может превышать 50',
                            val =>
                                val.length > 3 ||
                                'Поле ввода должно содержать не менее 3-х символов',
                        ]"
                    />
                    <VTextField
                        v-model="form.mail"
                        label="Email"
                        class="light-outline input-required"
                        outlined
                        dense
                        color="$color-purple-primary"
                        :rules="[
                            val => Boolean(val) || 'Укажите значение',
                            val =>
                                val.length > 3 ||
                                'Поле ввода должно содержать не менее 3-х символов',
                        ]"
                    />
                </v-form>
            </div>
            <div
                class="depon-images__data-images"
                :class="[{ 'depon-images__data-images--active': isDepon }]"
            >
                <div v-if="isDeponLoad" class="depon-images__loader-circular">
                    <VProgressCircular indeterminate size="50" color="accent" />
                </div>
                <div v-if="!isDepon" class="depon-images__data-images-sacsses">
                    <svg
                        width="71"
                        height="39"
                        viewBox="0 0 71 39"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M3.41675 19.5L20.2605 35.5417L28.6824 25.9167"
                            stroke="#A6AFBD"
                            stroke-width="5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M22.6667 19.4987L39.5105 35.5404L67.5834 3.45703"
                            stroke="#A6AFBD"
                            stroke-width="5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M48.3334 3.45703L37.1042 16.2904"
                            stroke="#A6AFBD"
                            stroke-width="5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <div>Все изображения в карточке защищены!</div>
                </div>
                <div v-if="isDepon" class="depon-images__data-images-info">
                    <span class="depon-images__data-images-info-alert">
                        <span v-if="escrow.remainLimit == 0">
                            В этом месяце лимит депонирования исчерпан
                        </span>
                        <span v-else-if="isDepon">Ожидают депонирования</span>
                    </span>
                </div>
                <div v-if="isDepon" class="depon-images__data-images-items">
                    <div class="depon-images__data-images-items-box">
                        <div
                            v-for="(item, index) in images"
                            :key="index"
                            class="depon-images__data-images-item"
                        >
                            <img :src="item" alt="" />
                        </div>
                    </div>
                </div>
                <div v-show="isDepon" class="depon-images__data-images-info">
                    <div class="depon-images__btn">
                        <VBtn
                            outlined
                            :disabled="escrow.remainLimit == 0"
                            class="filter-btn"
                            @click="deponStart"
                        >
                            Депонировать
                        </VBtn>
                    </div>
                    <span>Защитите ваши изоборажения</span>
                </div>
            </div>
        </div>

        <div class="depon-images__actions">
            <div class="depon-images__container-actions">
                <span class="depon-images__container-actions-text">
                    Ваши изображения использует кто-то ещё?
                </span>

                <div class="depon-images__btn">
                    <VBtn
                        color="accent"
                        :disabled="certificates.length == 0"
                        depressed
                        class="se-btn"
                        @click="isShowModal = true"
                    >
                        Сформировать претензию
                    </VBtn>
                </div>
            </div>
        </div>

        <VNavigationDrawer v-model="isShowModal" width="600" temporary fixed right>
            <div
                v-if="copyPretenz.visible"
                role="alert"
                class="depon-images__notification v-notification v-notification--standard"
                :class="`v-notification-${copyPretenz.type}`"
            >
                <div class="v-notification__wrapper">
                    <div class="v-notification__content">
                        <div class="v-notification__message col">
                            {{ copyPretenz.text }}
                        </div>
                    </div>
                    <div class="v-notification__actions">
                        <button
                            type="button"
                            class="
                                v-btn v-btn--icon v-btn--round
                                theme--light
                                v-size--small
                                transparent--text
                            "
                            @click="copyPretenz.visible = false"
                        >
                            <span class="v-btn__content">
                                <svg viewBox="0 0 24 24" role="img" aria-hidden="true">
                                    <path
                                        fill="currentColor"
                                        d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"
                                    />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="depon-images__form-box">
                <div class="depon-images__form-head">
                    <VBtn fab absolute style="left: -70px" @click="isShowModal = false">
                        <VIcon color="base-900">$close</VIcon>
                    </VBtn>

                    <h2 class="depon-images__form-hear-title">Сформировать претензию</h2>
                    <p class="depon-images__form-hear-text">
                        Создайте обращение в {{ isSelectedMp.name }}
                    </p>
                </div>
                <div class="depon-images__step-form">
                    <div class="depon-images__step-form-item">
                        <div v-if="step == 1" class="depon-images__step-form-item--active">
                            <div class="depon-images__step-form-item--active-num">1</div>
                            <div class="depon-images__step-form-item--active-content">
                                <VTextField
                                    v-model="linkPretenz"
                                    class="depon-images__step-form-item--active-content-input"
                                    label="Ссылка на товар конкурента"
                                    outlined
                                    dense
                                    color="$color-purple-primary"
                                    :rules="[val => Boolean(val) || 'Укажите значение']"
                                />
                            </div>
                            <VBtn
                                color="accent"
                                depressed
                                class="se-btn depon-images__step-form-item--active-content-btn"
                                @click="pretenzStart"
                            >
                                Применить
                            </VBtn>
                        </div>
                        <div v-if="step > 1" class="depon-images__step-form-item--sacess">
                            <div class="depon-images__step-form-item--sacess-num">1</div>
                            <div class="depon-images__step-form-item--sacess-text">Выполнено</div>
                            <div class="depon-images__step-form-item--sacess-img">
                                <img src="/images/icons/sacessIcon.svg" />
                            </div>
                        </div>
                    </div>

                    <div class="depon-images__step-form-item">
                        <div v-if="step < 2" class="depon-images__step-form-item--dis">
                            <div class="depon-images__step-form-item--dis-num">2</div>
                            <div class="depon-images__step-form-item--dis-text">
                                Скачать претензию
                            </div>
                        </div>
                        <div v-if="step == 2" class="depon-images__step-form-item--active">
                            <div class="depon-images__step-form-item--active-num">2</div>
                            <div class="depon-images__step-form-item--active-content">
                                <p>
                                    Скачайте и распечатайте претензию. Подпишите ее и поставьте
                                    печать организации, если она есть. Отсканируйте документ
                                </p>
                            </div>
                            <VBtn
                                color="accent"
                                depressed
                                class="se-btn depon-images__step-form-item--active-content-btn"
                                @click="downloadFile"
                            >
                                Скачать
                            </VBtn>
                        </div>
                        <div v-if="step > 2" class="depon-images__step-form-item--sacess">
                            <div class="depon-images__step-form-item--sacess-num">2</div>
                            <div class="depon-images__step-form-item--sacess-text">Выполнено</div>
                            <div class="depon-images__step-form-item--sacess-img">
                                <img src="/images/icons/sacessIcon.svg" />
                            </div>
                        </div>
                    </div>

                    <div class="depon-images__step-form-item">
                        <div v-if="step < 3" class="depon-images__step-form-item--dis">
                            <div class="depon-images__step-form-item--dis-num">3</div>
                            <div class="depon-images__step-form-item--dis-text">
                                Сертификат депонирования
                            </div>
                        </div>
                        <div v-if="step == 3" class="depon-images__step-form-item--active">
                            <div class="depon-images__step-form-item--active-num">3</div>
                            <div class="depon-images__step-form-item--active-content">
                                <p>Скачайте сертификат депонирования</p>
                            </div>
                            <VBtn
                                color="accent"
                                depressed
                                class="se-btn depon-images__step-form-item--active-content-btn"
                                @click="downloadFile"
                            >
                                Скачать
                            </VBtn>
                        </div>
                        <div v-if="step > 3" class="depon-images__step-form-item--sacess">
                            <div class="depon-images__step-form-item--sacess-num">3</div>
                            <div class="depon-images__step-form-item--sacess-text">Выполнено</div>
                            <div class="depon-images__step-form-item--sacess-img">
                                <img src="/images/icons/sacessIcon.svg" />
                            </div>
                        </div>
                    </div>

                    <div class="depon-images__step-form-item">
                        <div v-if="step < 4" class="depon-images__step-form-item--dis">
                            <div class="depon-images__step-form-item--dis-num">4</div>
                            <div class="depon-images__step-form-item--dis-text">
                                Текст обращения в поддержку
                            </div>
                        </div>
                        <div v-if="step == 4" class="depon-images__step-form-item--active">
                            <div class="depon-images__step-form-item--active-num">4</div>
                            <div>
                                <div style="display: flex">
                                    <div class="depon-images__step-form-item--active-content">
                                        <p>Скопируйте текст обращения в поддержку</p>
                                        <a
                                            v-if="!showText"
                                            class="depon-images__link"
                                            href="#"
                                            @click.prevent="showText = true"
                                        >
                                            Посмотреть
                                        </a>
                                    </div>
                                    <div class="">
                                        <VBtn
                                            color="accent"
                                            depressed
                                            class="
                                                se-btn
                                                depon-images__step-form-item--active-content-btn
                                            "
                                            @click="copyToPretenz"
                                        >
                                            Скопировать
                                        </VBtn>
                                        <div
                                            v-if="copyPretenz.visible"
                                            :class="`depon-images__step-form-item-info--${copyPretenz.type}`"
                                            class="depon-images__step-form-item-info"
                                        >
                                            {{ copyPretenz.text }}
                                        </div>
                                    </div>
                                </div>
                                <div v-if="showText" style="padding: 25px 0 0 25px">
                                    <p style="font-size: 14px">
                                        Здравствуйте. В карточке товара
                                        {{ linkPretenz }} размещены изображения из карточки
                                        {{ info.url }}, авторами которых является
                                        {{
                                            certificates[0].copyright_holder
                                                ? certificates[0].copyright_holder
                                                : certificates[0].full_name
                                        }}
                                        ({{ certificates[0].link }}). При этом наша карточка
                                        депонирована. Сертификат о депонировании и соответствующая
                                        претензия - во вложениях. Изображения размещены без нашего
                                        согласия. На этом основании просим скрыть из продажи товар
                                        {{ linkPretenz }}
                                    </p>

                                    <a
                                        class="depon-images__link"
                                        href="#"
                                        @click.prevent="showText = false"
                                    >
                                        Скрыть
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-if="step > 4" class="depon-images__step-form-item--sacess">
                            <div class="depon-images__step-form-item--sacess-num">4</div>
                            <div class="depon-images__step-form-item--sacess-text">Выполнено</div>
                            <div class="depon-images__step-form-item--sacess-img">
                                <img src="/images/icons/sacessIcon.svg" />
                            </div>
                        </div>
                    </div>

                    <div class="depon-images__step-form-item">
                        <div v-if="step < 5" class="depon-images__step-form-item--dis">
                            <div class="depon-images__step-form-item--dis-num">5</div>
                            <div class="depon-images__step-form-item--dis-text">
                                Переход в {{ isSelectedMp.id == 1 ? 'OZON Seller' : 'WB Partners' }}
                            </div>
                        </div>
                        <div v-if="step == 5" class="depon-images__step-form-item--active">
                            <div class="depon-images__step-form-item--active-num">5</div>
                            <div class="depon-images__step-form-item--active-content">
                                <p>
                                    Переход в маркетплейс. Вставьте в обращение на претензионный
                                    отдел скопированный текст. Перед отправкой приложите к заявке
                                    претензию и сертификат
                                </p>
                            </div>
                            <a
                                class="
                                    se-btn
                                    depon-images__step-form-item--active-content-btn
                                    v-btn v-btn--has-bg v-btn--router
                                    theme--light
                                    v-size--default
                                    accent
                                "
                                target="_blank"
                                :href="linkFinih"
                                @click="step = 1"
                            >
                                Перейти
                            </a>
                        </div>
                    </div>
                </div>
                <div class="depon-images__form-footer">
                    <VBtn
                        :disabled="step != 5"
                        block
                        large
                        color="accent"
                        @click="isShowModal = false"
                    >
                        Закрыть форму
                    </VBtn>
                </div>
            </div>
        </VNavigationDrawer>
    </div>
</template>
<script>
    import { mapGetters, mapState, mapActions, mapMutations } from 'vuex';
    import { format } from 'date-fns';
    import onboarding from '~mixins/onboarding.mixin';

    export default {
        name: 'DeponImages',
        mixins: [onboarding],
        props: {
            info: {
                type: Object,
                default: null,
            },
        },
        data: () => ({
            isDeponLoad: false,
            valid: true,
            form: {
                name: '',
                fio: '',
                mail: '',
            },
            getTabs: [
                { name: 'black', id: 0 },
                { name: 'blue', id: 1 },
            ],
            isShowModal: false,
            linkPretenz: '',
            urlPretenz: '',
            showText: false,
            step: 1,
            isDepon: false,
            dataImages: [],

            copyPretenz: {
                visible: false,
                text: '',
                type: '',
            },
        }),
        computed: {
            ...mapState(['auth']),
            ...mapState('product', ['hashes', 'certificates', 'escrow']),
            ...mapGetters({
                marketplaceSlug: 'getSelectedMarketplaceSlug',
                getImages: 'product/getImages',
                getWbImages: 'product/getWbImages',
            }),
            ...mapGetters(['isSelectedMp']),
            ...mapGetters('product', ['getActiveOptionIndex']),

            options() {
                return this.info.options.map(item => ({
                    name: item.name,
                    nmId: item.nmId,
                    image: item.images[0],
                }));
            },
            images() {
                return this.filterImages(this.dataImages, this.hashes);
            },
            linkFinih() {
                let url = '';
                if (this.isSelectedMp.id === 2) {
                    url = 'https://seller.wildberries.ru/service-desk/requests/create';
                }
                if (this.isSelectedMp.id === 1) {
                    url = 'https://seller.ozon.ru/app/main?helpCenter=create-issue';
                }
                return url;
            },
            ozonData() {
                const sku = Object.assign({}, this.info.sku);
                delete sku['fbs'];

                return Object.keys(sku).map(key => ({
                    id: sku[key],
                    url: this.info.url,
                    color: this.info.quantity[key] ? '#20c274' : '#fc6e90',
                    bgColor: this.info.quantity[key]
                        ? 'rgba(32, 194, 116, 0.08)'
                        : 'rgba(255, 11, 153, 0.08)',
                    icon: this.info.quantity[key] ? 'check' : 'close',
                    label: key.toUpperCase(),
                }));
            },
        },
        watch: {
            images() {
                this.isDepon = this.images.length > 0;
            },
            getActiveOptionIndex() {
                if (this.isSelectedMp.id === 2) {
                    this.dataImages =
                        this.getWbImages[this.options[this.getActiveOptionIndex].nmId];
                }
            },
            certificates(val) {
                this.form.name = val[0]?.copyright_holder || '';
                this.form.fio = val[0]?.full_name || '';
            },
        },
        mounted() {
            this.getDataCertificates();
            this.createOnBoarding();
        },
        methods: {
            ...mapActions('product', ['fetchCertificatesWB']),
            ...mapMutations('product', ['setField']),
            createOnBoarding() {
                if (!this.$refs.deponImagesInfo) {
                    return false;
                }

                const inputs = this.$refs.deponImagesInfo.querySelectorAll('.v-input__slot');
                const btns = this.$el.getElementsByClassName('depon-images__btn');

                let elements = [
                    {
                        el: inputs[0],
                        intro: 'Укажите ваше название продавца на маркетплейсе',
                        pos: 'top',
                    },
                    {
                        el: inputs[1],
                        intro: 'Укажите автора изображений. Если вы не знаете имя автора - напишите свое ФИО',
                        pos: 'bottom',
                    },
                    {
                        el: inputs[2],
                        intro: 'Проверьте почту. На указанный адрес вам может прийти ответ от сотрудников маркетплейса',
                        pos: 'bottom',
                    },
                ];

                if (!this.isDepon) {
                    elements = [
                        ...elements,
                        {
                            el: btns[0],
                            intro: 'Нажмите, чтобы получить свидетельство депонирования карточки',
                            pos: 'bottom',
                        },
                        {
                            el: btns[1],
                            intro: 'Нажмите, чтобы сформировать претензию и направить ее на маркетплейс',
                            pos: 'top',
                            forced: true,
                        },
                    ];
                }

                const createOnBoardingParams = {
                    elements,
                    routeNameFirstStep: this.$route.name,
                    isDisplayOnboard: true,
                };

                this.createOnBoardingByParams(createOnBoardingParams);
            },
            filterImages(imgs, hashes = []) {
                if (this.certificates.length == 0) {
                    return imgs;
                }
                return imgs.filter(item => {
                    let flag = true;
                    for (let i = 0; i < hashes.length; i++) {
                        if (item.indexOf(hashes[i].name) !== -1) {
                            flag = false;
                            break;
                        }
                    }
                    return flag;
                });
            },

            formatDate(date) {
                return format(new Date(date), 'dd.MM.yyyy');
            },

            getDataCertificates() {
                this.form.mail = this.auth.user.email;

                if (this.isSelectedMp.id === 2) {
                    this.dataImages =
                        this.getWbImages[this.options[this.getActiveOptionIndex].nmId];
                    this.getCertificatesWB();
                } else {
                    this.dataImages = this.getImages;
                }
            },

            getCertificatesWB() {
                this.fetchCertificatesWB(this.options[this.getActiveOptionIndex].nmId);
            },

            async deponStart() {
                try {
                    await this.$refs.form.validate();
                    if (!this.valid) {
                        return;
                    }

                    this.isDeponLoad = true;

                    const url = [
                        '/api/vp/v2/ozon/make-escrow',
                        '/api/vp/v2/wildberries/make-escrow',
                    ][this.isSelectedMp.id - 1];

                    const params = {
                        product_id: this.info.id,
                        email: this.form.mail,
                        full_name: this.form.fio,
                        copyright_holder: this.form.name,
                    };

                    if (this.isSelectedMp.id === 2) {
                        params['nmid'] = this.options[this.getActiveOptionIndex].nmId;
                    }

                    const { data } = await this.$axios.$post(url, params);
                    this.setField({ field: 'hashes', value: data.hashes });
                    this.setField({ field: 'certificates', value: data.certificates });
                    this.isDeponLoad = false;
                } catch (error) {
                    const errorsList = Object.values(error.response.data.error.advanced);
                    errorsList.forEach(error => {
                        this.$notify.create({
                            message: error,
                            type: 'negative',
                        });
                    });
                }
            },

            async pretenzStart() {
                try {
                    const url = [
                        '/api/vp/v2/ozon/get-abuse-pdf',
                        '/api/vp/v2/wildberries/get-abuse-pdf',
                    ][this.isSelectedMp.id - 1];

                    const {
                        data: {
                            data: { link },
                        },
                    } = await this.$axios({
                        method: 'GET',
                        url,
                        params: {
                            product_id: this.info.id,
                            holder: this.form.fio,
                            certificate_link: this.certificates[0].link,
                            site: `https://www.${this.marketplaceSlug}.ru`,
                            self_product_link: this.info.url,
                            another_product_link: this.linkPretenz,
                        },
                    });
                    this.urlPretenz = link;
                    this.step++;
                } catch (error) {
                    const errorsList = Object.values(
                        JSON.parse(await error.response.data.text()).error.advanced
                    );
                    errorsList.forEach(error => {
                        this.$notify.create({
                            message: error,
                            type: 'negative',
                        });
                    });
                }
            },

            // step3pretenz() {
            //     this.downloadFile(`${process.env.API_URL}${this.urlPretenz}`, 'pretenzia');
            //     this.step++;
            // },

            // step4pretenz() {
            //     this.downloadFile(this.certificates[0].link, 'certificat');
            //     this.step++;
            // },

            downloadFile() {
                const file = [
                    { url: `${process.env.API_URL}${this.urlPretenz}`, name: 'pretenzia' },
                    { url: this.certificates[0].link, name: 'certificat' },
                ][this.step - 2];

                const link = document.createElement('a');
                link.setAttribute('target', '_blank');
                link.setAttribute('href', file.url);
                link.setAttribute('download', `${file.name}.pdf`);
                document.body.appendChild(link);
                link.click();

                this.step++;
            },

            copyToPretenz() {
                const text = `Здравствуйте. В карточке товара ${
                    this.linkPretenz
                } размещены изображения из карточки ${this.info.url}, авторами которых является ${
                    this.certificates[0].copyright_holder
                        ? this.certificates[0].copyright_holder
                        : this.certificates[0].full_name
                } (${
                    this.certificates[0].link
                }). При этом наша карточка депонирована. Сертификат о депонировании и соответствующая претензия - во вложениях. Изображения размещены без нашего согласия. На этом основании просим скрыть из продажи товар ${
                    this.linkPretenz
                }`;

                this.$copyText(text).then(
                    e => {
                        this.step++;
                        this.copyPretenz = {
                            visible: true,
                            text: 'Текст скопирован',
                            type: 'positive',
                        };
                    },
                    e => {
                        this.copyPretenz = {
                            visible: true,
                            text: 'Ошибка копирования',
                            type: 'negative', // positive
                        };
                    }
                );
            },
        },
    };
</script>
<style lang="scss">
    .depon-images {
        padding: 16px;

        &__notification {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 999;
        }

        &__loader-circular {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 8;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
        }

        &__form-box {
            overflow: auto;
            display: flex;
            flex-flow: column nowrap;
            height: 100%;
        }

        &__form-head {
            padding: 16px;
        }

        &__form-hear-title {
            font-size: 24px;
            font-style: normal;
            font-weight: 500;
            line-height: 33px;
            color: $color-main-font;
        }

        &__form-hear-text {
            font-size: 14px;
            font-style: normal;
            font-weight: normal;
            line-height: 19px;
            color: $color-main-font;
        }

        &__link {
            display: inline-block;
            margin-top: 5px;
            padding: 4px 0;
            font-size: 16px;
            font-style: normal;
            font-weight: 500;
            line-height: 22px;
            color: $color-purple-primary;
        }

        &__link-certificates {
            display: inline-block;
            margin-top: 5px;
            margin-left: 5px;
            padding: 4px 0;
            font-size: 14px;
            font-style: normal;
            font-weight: 500;
            line-height: 24px;
            color: $color-purple-primary;
        }

        &__title {
            font-size: 24px;
            font-style: normal;
            font-weight: 500;
            line-height: 33px;
            color: $color-main-font;
        }

        &__descript {
            padding-bottom: 32px;
            font-size: 14px;
            font-style: normal;
            font-weight: normal;
            line-height: 19px;
            color: $color-main-font;
        }

        &__info {
            display: flex;
            width: 100%;
        }

        &__info-additional {
            margin-top: 16px;
        }

        &__data-tovar {
            width: 50%;
            margin-right: 12px;
        }

        &__data-tovar-contener {
            display: flex;
        }

        &__data-tovar-img {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid $color-gray-blue-light;

            & img {
                width: 200px;
            }
        }

        &__data-tovar-text {
            flex-grow: 1;
            padding-left: 24px;
        }

        &__data-tovar-item-text {
            padding: 8px 0;
        }

        &__data-tovar-item-title {
            font-size: 12px;
            font-style: normal;
            font-weight: 500;
            line-height: 16px;
            color: $color-gray-dark;
        }

        &__data-tovar-item-value {
            font-size: 16px;
            font-style: normal;
            font-weight: normal;
            line-height: 24px;
            color: $color-main-font;
        }

        &__fb-element {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;

            &:last-child {
                margin-bottom: 0;
            }
        }

        &__data-images {
            position: relative;
            display: flex;
            flex-flow: column nowrap;
            width: 50%;
            margin-left: 12px;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid $success;

            &--active {
                border: 1px solid $color-pink-dark;
            }
        }

        &__data-images-sacsses {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column nowrap;
            width: 100%;
            height: 100%;

            & div {
                padding: 20px 0;
            }
        }

        &__data-images-items {
            flex-grow: 1;
        }

        &__data-images-items-box {
            display: flex;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        &__data-images-item {
            overflow: hidden;
            display: flex;
            align-items: center;
            width: calc(calc(100% - 48px) / 6);
            height: 145px;
            margin: 8px 8px 0 0;
            border-radius: 8px;
            border: 1px solid $color-gray-light;

            & img {
                width: 100%;
            }
        }

        &__data-images-info {
            display: flex;
            align-items: center;
            height: 56px;
            padding: 0 8px;
            border-radius: 8px;
            background: $color-gray-light;

            & button {
                background: #fff;
            }

            & > span {
                padding: 0 16px;
                font-size: 16px;
                font-style: normal;
                font-weight: normal;
                line-height: 24px;
                color: $color-main-font;
            }
        }

        &__data-images-info-alert {
            padding: 0 8px;
            font-size: 16px;
            font-style: normal;
            font-weight: 500;
            line-height: 22px;
            color: $color-pink-dark;
        }

        &__form-title {
            padding: 16px 0;
            font-size: 14px;
            font-style: normal;
            font-weight: bold;
            line-height: 19px;
            color: $color-gray-dark;
        }

        &__actions {
            position: fixed;
            right: 0;
            bottom: 0;
            z-index: 3;
            width: 100%;
            background: $color-light-background;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.06);
        }

        &__container-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 32px;
        }

        &__container-actions-text {
            padding-right: 24px;
            font-size: 16px;
            font-style: normal;
            font-weight: normal;
            line-height: 24px;
            color: $color-main-font;
        }

        &__step-form {
            box-sizing: border-box;
            width: 100%;
            padding: 16px;
        }

        &__step-form-item {
            padding: 8px 0;
        }

        &__step-form-item--dis {
            display: flex;
            align-items: center;
            padding: 20px 36px;
            border-radius: 8px;
            border: 1px solid $color-gray-blue-light;
        }

        &__step-form-item--dis-num {
            font-size: 70px;
            line-height: 50px;
            color: $color-gray-light;
            font-weight: bold;
        }

        &__step-form-item--dis-text {
            padding: 0 36px;
            font-size: 16px;
            font-style: normal;
            font-weight: normal;
            line-height: 24px;
            color: $color-gray-dark;
        }

        &__step-form-item--sacess {
            display: flex;
            align-items: center;
            padding: 20px 36px;
            border-radius: 8px;
            background: $color-main-background;
        }

        &__step-form-item--sacess-num {
            font-size: 70px;
            line-height: 50px;
            color: $color-gray-blue-light;
            font-weight: bold;
        }

        &__step-form-item--sacess-text {
            padding: 0 36px;
            font-size: 16px;
            font-style: normal;
            font-weight: normal;
            line-height: 24px;
            color: $color-gray-dark;
        }

        &__step-form-item--sacess-img {
            display: flex;
            justify-content: flex-end;
            flex-grow: 1;
        }

        &__step-form-item--active {
            display: flex;
            align-items: center;
            padding: 29px;
            border-radius: 8px;
            border: 1px solid $color-purple-primary;
            box-shadow: 0 8px 16px rgba(113, 11, 255, 0.12);
        }

        &__step-form-item--active-num {
            font-size: 84px;
            line-height: 68px;
            color: $color-purple-primary;
            font-weight: bold;
        }

        &__step-form-item--active-content {
            flex-grow: 1;
            padding: 0 25px;
        }

        &__step-form-item--active-content-input {
            position: relative;
            top: 15px;
        }

        &__step-form-item--active-content-btn {
            width: 158px;
        }

        &__step-form-item-info {
            padding: 10px 0;
            font-size: 14px;
            color: #07c731;

            &--error {
                color: #c21313;
            }
        }

        &__form-footer {
            display: flex;
            align-items: flex-end;
            flex-grow: 1;
            padding: 16px;
        }

        &__tabs {
            padding-bottom: 25px;
        }

        &__tab {
            margin-left: 0 !important;
            padding: 0 20px 0 0;
        }

        &__active-tab {
            background-color: transparent;

            &:before {
                content: none;
            }
        }

        &__active-tab &__tab-btn {
            border-color: $primary-500;
            box-shadow: 0 0.2rem 1rem rgba(95, 69, 255, 0.16);
        }

        &__tab-btn span {
            font-weight: bold;
            font-size: 1rem;
            letter-spacing: normal;
            color: $color-main-font;
        }
    }

    @media screen and (max-width: 880px) {
        .depon-images {
            &__info {
                display: block;
            }

            &__data-tovar {
                width: 100%;
                margin-right: 0;
            }

            &__data-images {
                width: 100%;
                margin-left: 0;
            }

            &__container-actions {
                flex-wrap: wrap;
            }
        }
    }

    @media screen and (max-width: 470px) {
        .depon-images {
            &__data-tovar-contener {
                display: block;
            }

            &__data-tovar-img {
                text-align: center;
            }

            &__data-tovar-text {
                padding-left: 0;
            }
        }
    }
</style>
