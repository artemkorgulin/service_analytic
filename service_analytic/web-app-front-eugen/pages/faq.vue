<template>
    <div :class="$style.FAQ">
        <Page :title="title">
            <div :class="$style.pageContent">
                <VExpansionPanels ref="selectionTab" v-model="selectedTab" accordion>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Промо-тариф
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            В промо-версии SellerExpert вы можете отслеживать не более 3 товаров.
                            Чтобы добавить больше товаров и использовать все функции,
                            <NuxtLink to="/tariffs" :class="$style.Link">выберите тариф</NuxtLink>
                            .
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <!--                РАЗДЕЛ НИЖЕ ПОЗЖЕ ВЕРНЁМ -->
                    <!--                <VExpansionPanel>-->
                    <!--                    <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">-->
                    <!--                        Какой тариф выбрать?-->
                    <!--                    </VExpansionPanelHeader>-->
                    <!--                    <VExpansionPanelContent :class="$style.ExpansionPanelContent">-->
                    <!--                        <div :class="$style.ExpansionPanelContentGrid">-->
                    <!--                            <div>-->
                    <!--                                <p>Выберите оптимальную для вас подписку:</p>-->
                    <!--                                <p>-->
                    <!--                                    Тарифы отличаются количеством товаров, доступных для-->
                    <!--                                    отслеживания и редактирования, набором функций сервиса и сроком-->
                    <!--                                    подписки. Определите, сколько товаров вы хотите постоянно-->
                    <!--                                    отслеживать и выберите подходящий тариф.-->
                    <!--                                </p>-->
                    <!--                            </div>-->
                    <!--                            <VImg-->
                    <!--                                alt="Панель пользователя"-->
                    <!--                                contain-->
                    <!--                                src="/images/faq/2-1.gif"-->
                    <!--                                max-width="392"-->
                    <!--                            />-->
                    <!--                        </div>-->
                    <!--                    </VExpansionPanelContent>-->
                    <!--                </VExpansionPanel>-->
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Как оплатить подписку?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Вы можете оплатить подписку двумя способами: по карте или по
                                    счету.
                                </p>
                                <h4>По карте</h4>
                                <p>
                                    Чтобы оплатить банковской картой, выберите “по карте”. После
                                    успешной оплаты в платежной системе вы автоматически вернетесь в
                                    SellerExpert, а на Главной и в Настройках появится информация о
                                    тарифе и сроке подписки.
                                </p>
                                <h4>По счёту</h4>
                                <p>
                                    Чтобы оплатить безналичным способом, выберите “по счету”.
                                    Заполните информацию о компании. Вводить все данные вручную не
                                    надо: можно найти компанию по названию, ИНН или КПП в поле Поиск
                                    организации. Нажмите “сформировать счет” и скачайте документ.
                                    Как только оплата поступит на расчетный счет тариф будет
                                    активирован.
                                </p>
                                <SeAlert type="alert">
                                    <span class="font-weight-bold">Важно:</span>
                                    Если после оплаты подписки вы захотите увеличить количество
                                    товаров или расширить набор функций, вы сможете сменить тариф в
                                    любой момент, при этом оплата текущего тарифа не вернется.
                                </SeAlert>
                                <VImg
                                    alt="Оплата подписки"
                                    contain
                                    src="/images/faq/3-1.png"
                                    max-width="418"
                                />
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Как подключить маркетплейс?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    В разделе Мои маркетплейсы нажмите “Подключить маркетплейс” и
                                    выберите площадку, которую хотите добавить.
                                </p>
                                <VImg
                                    alt="Подключение к маркетплейсам"
                                    contain
                                    src="/images/faq/4-1.png"
                                    max-width="100%"
                                />
                                <p>
                                    Укажите произвольное название и ключи из вашего личного кабинета
                                    маркетплейса.
                                </p>
                                <VImg
                                    alt="Подключение к маркетплейсам"
                                    contain
                                    src="/images/faq/4-2.png"
                                    max-width="100%"
                                />
                                <p>
                                    После успешного добавления маркетплейса ключи появятся в
                                    таблице, а товары начнут загружаться.
                                    <a href="/faq?selection=6" :class="$style.Link">
                                        Подробнее об управлении товарами
                                    </a>
                                    .
                                </p>
                                <p>Вы можете добавить новый личный кабинет в любой момент.</p>
                                <VImg
                                    alt="Подключение к маркетплейсам"
                                    contain
                                    src="/images/faq/4-3.png"
                                    max-width="100%"
                                />
                                <SeAlert type="alert">
                                    Личный кабинет площадки можно подключить только к одной учетной
                                    записи SellerExpert. Разные пользователи не могут добавлять
                                    одинаковые маркетплейсы.
                                </SeAlert>
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Где найти ключи OZON Управление товарами?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Ключи OZON находятся в
                                    <a
                                        :class="$style.Link"
                                        href="https://seller.ozon.ru/app/advertisement/!#/settings/api-keys"
                                        target="_blank"
                                    >
                                        настройках личного кабинета
                                    </a>
                                    OZON Seller и доступны только администраторам и владельцам
                                    кабинетов.
                                </p>
                                <p>Скопируйте Client ID и вставьте в соответствующее поле</p>
                                <VImg
                                    alt="Личный кабинет"
                                    contain
                                    src="/images/faq/5-1.png"
                                    max-width="100%"
                                />
                                <p>Создайте и скопируйте API key:</p>
                                <VImg
                                    alt="Создайте и скопируйте API key"
                                    contain
                                    src="/images/faq/5-1.png"
                                    max-width="100%"
                                />
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Где найти ключи Wildberries Управление товарами?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Ключи Wildberries находятся в
                                    <a
                                        href="https://seller.wildberries.ru/supplier-settings/access-to-api"
                                        :class="$style.Link"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        настройках личного кабинета
                                    </a>
                                    WB Партнеры и доступны только владельцам кабинетов.
                                </p>
                                <p>
                                    Скопируйте Ключ для работы с API статистики x32 из раздела
                                    <a
                                        href="https://seller.wildberries.ru/supplier-settings/access-to-api"
                                        :class="$style.Link"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Доступ к API
                                    </a>
                                </p>
                                <VImg
                                    alt="Раздел Доступ к API"
                                    contain
                                    src="/images/faq/6-1.png"
                                    max-width="100%"
                                />
                                <p>
                                    Создайте и скопируйте API key из раздела
                                    <a
                                        href="https://seller.wildberries.ru/supplier-settings/access-to-new-api"
                                        :class="$style.Link"
                                        target="_blank"
                                        rel="noopener"
                                    >
                                        Доступ к новому API
                                    </a>
                                </p>
                                <VImg
                                    alt="Разел Доступ к новому API"
                                    contain
                                    src="/images/faq/6-2.png"
                                    max-width="100%"
                                />
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Как работать с товарами?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Для начала работы с товарами добавьте их в отслеживание в
                                    разделе Мои товары.
                                </p>
                                <p>
                                    В разделе Мои товары выберите нужный личный кабинет
                                    маркетплейса:
                                </p>
                                <VImg
                                    alt="личный кабинет"
                                    contain
                                    src="/images/faq/7-1.png"
                                    max-width="100%"
                                />
                                <p>
                                    Нажмите Добавить товар, в появившемся окне выберите нужный бренд
                                    и товары и добавьте их.
                                </p>
                                <SeAlert type="alert">
                                    После добавления аккаунта API маркетплейса загрузка товаров
                                    может занять некоторое время. Если товары не загрузились,
                                    напишите нам:
                                    <a href="mailto:team@sellerexpert.ru" :class="$style.Link">
                                        team@sellerexpert.ru
                                    </a>
                                </SeAlert>
                                <VImg
                                    alt="добавление товаров"
                                    contain
                                    src="/images/faq/7-2.png"
                                    max-width="100%"
                                />
                                <VImg
                                    alt="выберите товары"
                                    contain
                                    src="/images/faq/7-3.png"
                                    max-width="342"
                                />
                                <p>
                                    Выбранные товары добавятся в отслеживание: теперь вы сможете
                                    видеть по ним статистику, а также оптимизировать карточки. Чтобы
                                    попасть в карточку товара, кликните на название
                                </p>
                                <VImg
                                    alt="выберите товары"
                                    contain
                                    src="/images/faq/7-4.png"
                                    max-width="100%"
                                />
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Как работает контентная оптимизация?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Вы можете управлять ценой товара, редактировать характеристики
                                    или описание в разделе Контентная оптимизация. Обратите внимание
                                    на рекомендации: мы разработали их для каждого вашего товара.
                                    Чем выше уровень контентной оптимизации карточки, тем в большем
                                    количестве категорий будет показываться товар.
                                </p>
                                <VImg
                                    alt="Контентная оптимизация"
                                    contain
                                    src="/images/faq/9-1.png"
                                    max-width="100%"
                                />
                                <SeAlert type="alert">
                                    <span class="font-weight-bold">Важно:</span>
                                    обращайте внимание на изменение степени оптимизации при работе с
                                    карточкой. Если после редактирования карточки процент
                                    оптимизации уменьшился, измените введенные значения
                                    характеристик или укажите больше значений.
                                </SeAlert>
                                <p>
                                    Чтобы отслеживать эффективность контентной оптимизации карточки,
                                    воспользуйтесь графиком Динамика позиций в категории: он
                                    показывает позиции карточки товара во всех категориях, в которых
                                    она показывается.
                                </p>
                                <VImg
                                    alt="Контентная оптимизация"
                                    contain
                                    src="/images/faq/9-2.png"
                                    max-width="100%"
                                />
                                <p>
                                    Чем выше позиции карточки в категориях, тем больше пользователей
                                    увидят товар — это напрямую сказывается на росте продаж.
                                </p>
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Как работает поисковая оптимизация?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Чтобы подобрать ключевые запросы, укажите типы, которые подходят
                                    для вашего товара. Вы можете указать до трех разных типов товара
                                    одновременно
                                </p>
                                <VImg
                                    alt="Поисковая оптимизация"
                                    contain
                                    src="/images/faq/10-1.png"
                                    max-width="100%"
                                    width="100%"
                                />
                                <p>
                                    Для каждого выбранного алгоритм подберет ключевые запросы, по
                                    которым покупатели искали подобный товар на маркетплейсе.
                                    Обращайте внимание на популярность - она показывает, сколько
                                    покупателей ищут похожий товар.
                                </p>
                                <p>
                                    Отметьте галочками ключевые слова, которые хотите добавить в
                                    карточку, нажмите Применить. Выбранные слова появятся в столбце
                                    Ключевые запросы.
                                </p>
                                <VImg
                                    alt="Поисковая оптимизация"
                                    contain
                                    src="/images/faq/10-2.png"
                                    max-width="100%"
                                    width="100%"
                                />
                                <p>
                                    Выберите характеристику, в которую хотите добавить ключевой
                                    запрос и кликите на этот запрос
                                </p>
                                <VImg
                                    alt="Поисковая оптимизация"
                                    contain
                                    src="/images/faq/10-3.png"
                                    max-width="100%"
                                    width="100%"
                                />
                                <p>
                                    После добавления нужных ключевых запросов, отправьте изменения
                                    на площадку. После успешной модерации маркетплейсом карточка
                                    начнет показываться в новых поисковых запросах.
                                </p>
                                <p>
                                    Чтобы отслеживать эффективность поисковой оптимизации карточки,
                                    воспользуйтесь графиком Динамика позиций - Запросы: он
                                    показывает позиции карточки товара в основных поисковых
                                    запросах.
                                </p>
                                <VImg
                                    alt="Поисковая оптимизация"
                                    contain
                                    src="/images/faq/10-4.png"
                                    max-width="100%"
                                    width="100%"
                                />
                                <p>
                                    Чем выше позиции карточки в поиске, тем больше пользователей
                                    увидят товар — это напрямую сказывается на росте продаж.
                                </p>
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                    <VExpansionPanel>
                        <VExpansionPanelHeader :class="$style.ExpansionPanelHeader">
                            Как защитить авторство карточки товара?
                        </VExpansionPanelHeader>
                        <VExpansionPanelContent :class="$style.ExpansionPanelContent">
                            <div :class="$style.ExpansionPanelContentGrid">
                                <p>
                                    Во вкладке Защита авторства вы можете получить свидетельство
                                    депонирования карточки и сформировать претензию на маркетплейс в
                                    случае, если ваши изображения неправомерно используют.
                                </p>
                                <p>
                                    Свидетельство депонирования - официальный документ,
                                    подтверждающий ваше первоначальное авторство на медиа в карточке
                                    товара, а правильно составленная претензия поможет урегулировать
                                    вопрос на маркетплейсе.
                                </p>
                                <p>
                                    Чтобы получить свидетельство депонирования укажите информацию и
                                    правообладателе и нажмите “Депонировать”
                                </p>
                                <VImg
                                    alt="Защита авторств"
                                    contain
                                    src="/images/faq/11-1.png"
                                    max-width="100%"
                                    width="100%"
                                />
                            </div>
                        </VExpansionPanelContent>
                    </VExpansionPanel>
                </VExpansionPanels>
            </div>
        </Page>
    </div>
</template>

<script>
    import Page from '~/components/ui/SeInnerPage';

    export default {
        name: 'FAQ',
        components: {
            Page,
        },
        data() {
            return {
                title: {
                    isActive: true,
                    text: 'Обучение',
                },
                selectedTab: undefined,
            };
        },
        watch: {
            selectedTab(value) {
                if (value !== undefined) {
                    this.$router.push({ query: { selection: value + 1 } });
                }
            },
        },
        mounted() {
            const { selection } = this.$route.query;
            const {
                selectionTab: { $el: selectionTab },
            } = this.$refs;

            const selectionTabs = Array.from(
                selectionTab.getElementsByClassName('v-expansion-panel')
            );

            if (selection !== undefined) {
                this.selectedTab = selection - 1;
                this.goToTab(selectionTabs[this.selectedTab]);
            }
        },
        methods: {
            goToTab(tab) {
                setTimeout(() => {
                    tab.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                }, 500);
            },
        },
    };
</script>

<style lang="scss" module>
    .FAQ {
        display: grid;
        grid-template-columns: 1fr;
        grid-template-rows: auto;
        gap: size(16);

        & .pageHeading {
            @extend %text-h1;

            //margin-bottom: 24px;

            @include md {
                margin-bottom: 0;
                padding: 16px;
            }
        }

        & .ExpansionPanelHeader {
            font-size: 16px;
            font-weight: bold;
            line-height: percentage(22px/16px);
        }

        & .ExpansionPanelContent {
            font-size: 14px;
            line-height: percentage(22px/14px);
        }

        & .ExpansionPanelContentGrid {
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: auto;
            gap: size(24);

            & h4 {
                margin-bottom: -10px;
                font-size: 14px;
                font-weight: 600;
                line-height: 150%;
            }
        }

        & .Link {
            color: $primary-500;
            font-weight: bold;
        }

        & .PicturesFlexContainer {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
        }

        & .Caution {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
            border-radius: 8px;
            background: rgba(255, 11, 153, 0.04);
            color: $color-pink-dark;

            & .CautionIconWrapper {
                width: 18.2px;
            }

            & .CautionIcon {
                width: 18.2px;
                height: 16.7px;
            }

            & .Bold {
                font-weight: bold;
            }
        }

        ul > li {
            list-style: disc;
            line-height: 1.6;
        }

        & :global(.v-expansion-panel:before) {
            @include cardShadow;
        }

        & :global(.v-image) {
            image-rendering: -webkit-optimize-contrast;
        }

        & :global(.v-expansion-panel-header__icon) {
            width: 32px;
            min-width: 32px;
            height: 32px;
            border-radius: 8px;
            background-color: $base-100;

            & :global(.v-icon) {
                margin: auto;
                color: #000 !important;
                transform: rotate(-90deg);
            }

            & :global(.v-icon__svg) {
                width: 20px;
                height: 20px;
            }
        }
    }
</style>
