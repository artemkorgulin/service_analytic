<template>
    <div class="actions-users">
        <div class="actions-users__btn" @click="blockUser">Удалить</div>
        <!-- <v-menu offset-y bottom left>
            <template v-slot:activator="{ on, attrs }">
                <div class="actions-users__btn" v-bind="attrs" v-on="on"></div>
            </template>
            <v-list class="actions-users__list">
                <v-list-item>
                    <v-list-item-title @click="blockUser">Удалить</v-list-item-title>
                </v-list-item>
            </v-list>
        </v-menu> -->
    </div>
</template>

<script>
    import { mapActions } from 'vuex';
    export default {
        methods: {
            ...mapActions('companies', ['lockUser', 'getCompany']),
            async blockUser() {
                await this.lockUser({
                    id: this.params.data.id,
                    companyId: this.params.data.companyId,
                });
                this.getCompany(this.params.data.companyId);
            },
        },
    };
</script>

<style lang="scss">
    .actions-users {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        height: 100%;

        &__btn {
            padding: 4px 8px;
            line-height: 16px;
            cursor: pointer;

            &:hover {
                background: rgba(212, 212, 212, 0.575);
            }

            // width: 16px;
            // height: 16px;
            // background: url(/images/icons/menu.svg) no-repeat center/4px;

            // &:hover {
            //     background: url(/images/icons/menu-hov.svg) no-repeat center/4px;
            // }
        }
    }

    .actions-users__list.v-list {
        padding: 0;

        .v-list-item {
            min-height: 40px;
            padding: 0 16px;
            cursor: pointer;

            &:hover {
                background: #e9e9e9;
            }
        }
    }
</style>
