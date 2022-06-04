<template>
    <div class="companies">
        <VNavigationDrawer v-model="isShowModal" width="600" temporary fixed right>
            <div class="companies__form-box">
                <div class="companies__form-head">
                    <VBtn fab absolute style="left: -70px" @click="isShowModal = false">
                        <VIcon color="base-900">$close</VIcon>
                    </VBtn>
                    <h2 class="companies__form-hear-title">Добавление сотрудника</h2>
                </div>
                <div class="companies__step-form">
                    <div class="companies__step-form-item">
                        <div v-if="stepForm == 1" class="companies__step-form-item--active">
                            <div class="companies__step-form-item--active-num">1</div>
                            <div class="companies__step-form-item--active-content">
                                <div class="companies__form-mail-lable">
                                    Найдите сотрудника по Email
                                </div>
                                <div class="companies__form-mail-content">
                                    <VTextField
                                        v-model="userEmail"
                                        class="companies__step-form-item--active-content-input"
                                        label="Email"
                                        outlined
                                        dense
                                        color="$color-purple-primary"
                                        :rules="[val => Boolean(val) || 'Укажите Email']"
                                    />
                                    <VBtn
                                        color="accent"
                                        depressed
                                        class="se-btn companies__step-form-item--active-content-btn"
                                        @click="searchUser"
                                    >
                                        Найти
                                    </VBtn>
                                </div>
                                <div
                                    v-if="!dataUser && flagSearch"
                                    class="companies__form-mail-content-error"
                                >
                                    Пользователь с таким Email не найден.
                                    <br />
                                    Добавить можно только зарегистированных пользователей.
                                </div>
                            </div>
                        </div>
                        <div v-if="stepForm > 1" class="companies__step-form-item--sacess">
                            <div class="companies__step-form-item--sacess-num">1</div>
                            <div class="companies__step-form-item--sacess-text">Выполнено</div>
                            <div class="companies__step-form-item--sacess-img">
                                <img src="/images/icons/sacessIcon.svg" />
                            </div>
                        </div>
                    </div>
                    <div class="companies__step-form-item">
                        <div v-if="stepForm < 2" class="companies__step-form-item--dis">
                            <div class="companies__step-form-item--dis-num">2</div>
                            <div class="companies__step-form-item--dis-text">Права доступа</div>
                        </div>
                        <div v-if="stepForm == 2" class="companies__step-form-item--active">
                            <div class="companies__step-form-item--active-num">2</div>
                            <div class="companies__step-form-item--active-content">
                                <div class="companies__form-user">
                                    <div class="companies__form-user-info">
                                        <div class="companies__form-user-info-item">
                                            <div class="companies__form-user-info-item-lable">
                                                ФИО
                                            </div>
                                            <div class="companies__form-user-info-item-value">
                                                {{ dataUser.name }}
                                            </div>
                                        </div>
                                        <div class="companies__form-user-info-item">
                                            <div class="companies__form-user-info-item-lable">
                                                Email
                                            </div>
                                            <div class="companies__form-user-info-item-value">
                                                {{ dataUser.email }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="companies__form-user-lable">
                                        Задайте права пользователю
                                    </div>
                                    <VSelect
                                        v-model="userRole"
                                        :items="roles"
                                        item-text="description"
                                        item-value="name"
                                        :menu-props="{ maxHeight: '400' }"
                                        class="light-outline companies__form-user-select"
                                        label="Права доступа"
                                        multiple
                                        chips
                                        close
                                        deletable-chips
                                        persistent-hint
                                    ></VSelect>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="companies__form-footer">
                    <div class="companies__form-footer-content">
                        <div class="companies__form-footer-content-text">
                            Ваш сотрудник получит письмо с уведомлением.
                        </div>
                        <VBtn :disabled="stepForm == 5" block large color="accent" @click="addUser">
                            Добавить
                        </VBtn>
                    </div>
                </div>
            </div>
        </VNavigationDrawer>
        <Page :title="title" :btn-add="btnAdd" :btn-add-click="openModal">
            <div class="companies__content">
                <SePageTab
                    v-model="selectedTab"
                    :items="tabItems"
                    class="mb-5 companies__content-tabs"
                ></SePageTab>
                <div v-if="selectedTab === 0" class="companies__content-box">
                    <VSelect
                        v-model="selectCompanyId"
                        label="Сохраненные компании"
                        :items="thisCompanies"
                        item-text="name"
                        item-value="id"
                        class="per-page__select light-outline"
                        outlined
                        dense
                        hide-details
                        style="margin-bottom: 25px"
                        @change="getCompany"
                    />
                    <VTextField
                        v-model="form.name"
                        class="light-outline"
                        label="Наименование организации"
                        outlined
                        dense
                        color="$color-purple-primary"
                        :rules="[val => Boolean(val) || 'Укажите значение']"
                        @input="flagEdit = true"
                    />
                    <VTextField
                        v-model="form.inn"
                        class="light-outline"
                        label="ИНН"
                        outlined
                        dense
                        color="$color-purple-primary"
                        :rules="[val => Boolean(val) || 'Укажите значение']"
                        @input="flagEdit = true"
                    />
                    <VTextField
                        v-model="form.kpp"
                        class="light-outline"
                        label="КПП"
                        outlined
                        dense
                        color="$color-purple-primary"
                        :rules="[val => Boolean(val) || 'Укажите значение']"
                        @input="flagEdit = true"
                    />
                    <div class="">
                        <VBtn :disabled="!flagEdit" color="accent" @click="saveInfoCompany">
                            Сохранить
                        </VBtn>
                        <VBtn :disabled="!flagEdit" style="margin-left: 10px" text @click="notSave">
                            Отменить изменения
                        </VBtn>
                    </div>
                </div>

                <div v-if="selectedTab === 1" class="companies__content-box">
                    <SeTableAG ref="table" :columns="columnDefs" :rows="users" nostat nobar />
                </div>
            </div>
        </Page>
    </div>
</template>
<script>
    import SeTableAG from '~/components/ui/SeTableAG.vue';

    import { mapActions, mapState } from 'vuex';
    import Page from '~/components/ui/SeInnerPage';
    import actionCell from '~/components/pages/companies/actionCell';
    import actionCellRoles from '~/components/pages/companies/actionCellRoles';

    export default {
        components: {
            SeTableAG,
            Page,
            // eslint-disable-next-line vue/no-unused-components
            actionCell,
            // eslint-disable-next-line vue/no-unused-components
            actionCellRoles,
        },
        data: () => ({
            title: {
                isActive: true,
                text: 'Компании',
            },
            btnAdd: {
                isActive: true,
                isImg: true,
                text: 'Добавить сотрудника',
            },
            selectCompanyId: null,
            isShowModal: false,
            userEmail: '',
            userRole: [],
            flagSearch: false,
            dataUser: null,
            flagEdit: false,

            stepForm: 1,
            form: {
                name: '',
                inn: '',
                kpp: '',
            },

            selectedTab: 0,
            tabItems: [{ title: 'Данные о компании' }, { title: 'Сотрудники' }],

            columnDefs: [
                {
                    field: 'data',
                    headerName: 'Дата',
                    sortable: true,
                    width: 120,
                    cellRenderer: function (params) {
                        if (!params.data) {
                            return '';
                        }
                        const date = new Date(params.data.data);
                        const months = date.getMonth() + 1;
                        const mm = months > 9 ? months : `0${months}`;
                        const dd = date.getDate();
                        const yyyy = date.getFullYear();
                        return dd && mm && yyyy ? `${dd}.${mm}.${yyyy}` : '-';
                    },
                },
                {
                    field: 'name',
                    headerName: 'Имя Фамилия',
                    sortable: true,
                    filter: 'agTextColumnFilter',
                    flex: 1,
                },
                {
                    field: 'role',
                    headerName: 'Роль',
                    sortable: true,
                    cellRenderer: 'actionCellRoles',
                    filter: 'agTextColumnFilter',
                    filterParams: {
                        /* eslint-disable */
                        valueGetter: params =>
                            params.data.role.reduce(
                                (accumulator, item) => (accumulator += item.description),
                                ''
                            ),
                    },
                    flex: 2,
                },
                {
                    field: 'email',
                    headerName: 'Почта',
                    sortable: true,
                    filter: 'agTextColumnFilter',
                    flex: 1,
                },
                { headerName: 'Действия', cellRenderer: 'actionCell', flex: 1 },
                { field: 'id', headerName: '', hide: true },
                { field: 'companyId', headerName: '', hide: true },
            ],
        }),
        computed: {
            ...mapState('companies', ['companies', 'company', 'users', 'roles']),

            thisCompanies() {
                return this.companies.filter(item => item.permissions.includes('company.show'));
            },
        },
        watch: {
            company() {
                this.form = {
                    name: this.company.name,
                    inn: this.company.inn,
                    kpp: this.company.kpp,
                };
            },
        },
        async mounted() {
            await this.loadCompanies();
            this.loadRoles();
            this.selectCompanyId = this.companies.length > 0 ? this.companies[0].id : null;
            this.getCompany(this.selectCompanyId);
        },
        methods: {
            ...mapActions('companies', ['loadCompanies', 'getCompany', 'loadRoles']),
            saveInfoCompany() {
                this.flagEdit = false;
                this.$axios({
                    method: 'PUT',
                    url: `/api/v1/company/${this.selectCompanyId}`,
                    params: {
                        name: this.form.name,
                        inn: this.form.inn,
                        kpp: this.form.kpp,
                        adress: 'null',
                    },
                });
            },
            notSave() {
                this.form = {
                    name: this.company.name,
                    inn: this.company.inn,
                    kpp: this.company.kpp,
                };
            },

            async searchUser() {
                try {
                    const {
                        data: { data },
                    } = await this.$axios({
                        method: 'GET',
                        url: '/api/v1/user-search',
                        params: {
                            email: this.userEmail,
                            company_id: this.selectCompanyId,
                        },
                    });
                    if (data) {
                        this.dataUser = data;
                        this.stepForm = 2;
                    }
                    this.flagSearch = true;
                } catch (error) {
                    this.errorCatcher(error);
                }
            },

            async addUser() {
                try {
                    await this.$axios({
                        method: 'POST',
                        url: '/api/v1/user-company',
                        params: {
                            user_id: this.dataUser.id,
                            company_id: this.selectCompanyId,
                        },
                    });
                    await this.$axios({
                        method: 'POST',
                        url: `/api/v1/user-company-roles/${this.selectCompanyId}/${this.dataUser.id}`,
                        params: {
                            roles: this.userRole,
                        },
                    });
                    this.isShowModal = false;
                    this.userEmail = '';
                    this.userRole = [];
                    this.flagSearch = false;
                    this.dataUser = null;
                    this.stepForm = 1;

                    this.getCompany(this.selectCompanyId);
                } catch (error) {
                    this.errorCatcher(error);
                }
            },
            errorCatcher(error) {
                const errorsList = error.response.data.error.advanced;
                errorsList.forEach(error => {
                    this.$notify.create({
                        message: Object.values(error)[0],
                        type: 'negative',
                    });
                });
            },
            openModal() {
                this.isShowModal = true;
            },
        },
    };
</script>
<style lang="scss" scoped>
    .companies {
        &__form-mail {
            &-lable {
                font-size: 14px;
                line-height: 19px;
                color: #505965;
            }

            &-content {
                display: flex;

                & .se-btn {
                    margin-top: 14px;
                    margin-left: 10px;
                }

                &-error {
                    padding: 8px 8px 8px 42px;
                    border-radius: 8px;
                    border: 1px solid #ffb9d2;
                    background: url(/images/icons/error-email-role.svg) no-repeat 3% 50%/19px;
                    font-size: 14px;
                    line-height: 19px;
                    color: #2f3640;
                }
            }
        }

        &__form-user {
            &-info {
                display: flex;

                &-item {
                    width: 50%;

                    &-lable {
                        font-size: 12px;
                        line-height: 16px;
                        color: #7e8793;
                    }

                    &-value {
                        font-size: 16px;
                        line-height: 24px;
                        color: #2f3640;
                    }
                }
            }

            &-lable {
                padding-top: 30px;
                font-size: 14px;
                line-height: 19px;
                color: #505965;
            }
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

        &__form-footer {
            display: flex;
            align-items: flex-end;
            flex-grow: 1;
            padding: 16px;

            &-content {
                width: 100%;

                &-text {
                    padding-bottom: 16px;
                    font-size: 14px;
                    line-height: 19px;
                    color: #505965;
                }
            }
        }

        &__form-hear-title {
            text-align: center;
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
            padding: 0 0 0 25px;
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

        &__title {
            display: flex;
            align-items: center;
            padding: 18px 8px;
            background: #f9f9f9;

            &-box {
                display: flex;
                justify-content: space-between;
                flex: 1 1 auto;
            }

            &-prev {
                padding: 6px 16px 6px 12px;
                border-radius: 200px;
                border: 1px solid #c8cfd9;
                font-size: 14px;
                font-style: normal;
                font-weight: 500;
                line-height: 19px;
                color: #7e8793;
                cursor: pointer;

                & svg {
                    margin-right: 8px;
                }
            }

            &-text {
                padding: 0 12px;
                font-size: 24px;
                font-style: normal;
                font-weight: 500;
                line-height: 33px;
                color: #2f3640;
            }
        }

        &__content {
            padding: 16px;
            border-radius: 16px;
            background: #fff;

            @include cardShadow;

            &-tabs {
                display: inline-block;
            }
        }
    }
</style>
<style lang="scss">
    .companies {
        .ag-cell-value {
            overflow: visible;
        }

        &__form-user-select {
            & .v-select__slot {
                border-radius: 8px;
                border: 1px solid #c8cfd9;
            }

            & .v-input__slot {
                &:after,
                &:before {
                    display: none;
                }
            }

            & label {
                top: 10px !important;
                left: 10px !important;
                background: #fff;
            }

            & .v-input__append-inner {
                padding-right: 5px;
            }

            & label.v-label--active {
                top: 8px !important;
            }

            & .v-chip {
                padding: 8px;
                border-radius: 4px;
                background: #6610dc !important;
                color: #fff !important;

                & .v-chip__content {
                    padding: 0;
                }

                & button {
                    background: url(/images/icons/close.svg) no-repeat center/9px;

                    & svg {
                        display: none;
                    }
                }
            }
        }

        &__cell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }

        &__status {
            padding: 4px 16px;
            border-radius: 200px;
            font-weight: 700;
            font-size: 12px;
            line-height: 16px;
            color: #fff;

            &.active {
                background: #20c274;
            }

            &.noactive {
                background: #ff3981;
            }
        }
    }

    .actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        width: 100%;
        height: 100%;

        &__menu-content-item {
            padding: 0 10px;
            border-radius: 5px;
            border: 1px solid #cdd4dd;
            line-height: 24px;
            cursor: pointer;

            &:hover {
                background: #e9e9e9;
            }
        }
    }
</style>
