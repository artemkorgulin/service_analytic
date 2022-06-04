<template>
    <div class="actions-roles">
        <v-select
            class="per-page__select light-outline"
            v-model="rolesUser"
            :menu-props="{ contentClass: 'se-select__menu', eager: true, offsetY: true }"
            :items="rolesAll"
            item-text="description"
            item-value="name"
            multiple
            @blur="updateRoles"
        >
            <template v-slot:item="{ item, attrs, on }">
                <v-list-item v-on="on" v-bind="attrs" #default="{ active }">
                    <v-list-item-action>
                        <BaseCheckbox :value="active" />
                    </v-list-item-action>

                    <v-list-item-content>
                        <v-list-item-title> {{ item.description }} </v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
            </template>
        </v-select>
    </div>
</template>

<script>
    import { mapState, mapGetters, mapActions } from 'vuex';
    import { errorHandler } from '~utils/response.utils';
    export default {
        data() {
            return {
                rolesUserThis: [],
            };
        },
        created() {
            this.rolesUserThis = this.params.data.role.map(item => item.name);
        },
        computed: {
            ...mapGetters('companies', ['rolesAll']),
            ...mapState('companies', ['roles']),
            rolesUser: {
                get() {
                    return this.params.data.role.map(item => item.name);
                },
                set(value) {
                    this.rolesUserThis = value;
                },
            },
        },
        methods: {
            ...mapActions('companies', ['getCompany']),
            async updateRoles() {
                try {
                    const topic = `/api/v1/user-company-roles/${this.params.data.companyId}/${this.params.data.id}`;
                    const deleteItem = this.rolesUser.filter(
                        item => !this.rolesUserThis.includes(item)
                    );
                    const addItem = this.rolesUserThis.filter(
                        item => !this.rolesUser.includes(item)
                    );
                    if (addItem.length) {
                        await this.$axios.post(topic, {
                            roles: addItem,
                        });
                    }
                    if (deleteItem.length) {
                        await this.$axios.delete(topic, {
                            params: {
                                roles: deleteItem,
                            },
                        });
                    }
                    this.getCompany(this.params.data.companyId);
                } catch (error) {
                    console.error(error);
                    errorHandler(error, this.$notify);
                }
            },
        },
    };
</script>

<style lang="scss">
    .actions-roles {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        width: 100%;
        height: 100%;

        .per-page__select {
            position: relative;
            top: 3px;
        }

        .v-input__slot {
            padding-left: 4px;
            border-radius: 4px;
            font-size: 12px;

            &:hover {
                background: rgba(212, 212, 212, 0.575);
            }

            &:before {
                display: none;
            }
        }
    }
</style>
