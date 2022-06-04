<template>
    <div>
        <div class="row justify-content-center">
            <h3 class="text-center">Создание нового пользователя</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-4">
                <label>ФИО</label>
                <input type="text" class="form-control" placeholder="Фамилия Имя Отчество"  aria-label="фио" v-model="name">
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label>email</label>
                <input type="text" class="form-control" placeholder="электронная почта" aria-label="email" v-model="email">
            </div>

        </div>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label>телефон</label>
                <input type="text" class="form-control" placeholder="телефон пользователя" aria-label="phone" v-model="phone">
            </div>

        </div>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label>пароль</label>
                <input type="text" class="form-control" placeholder="password" aria-label="password" v-model="password">
            </div>

        </div>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <label>подтвердить пароль</label>
                <input type="text" class="form-control" placeholder="подтвердить пароль" aria-label="password" v-model="confirm_password">
            </div>

        </div>
        <br>
        <div class="row justify-content-center">

            <div class="col-md-4 d-grid">
                <button @click="addUser" class="btn btn-primary">создать</button>
            </div>

        </div>

    </div>
</template>

<script>
import state from "../../store/state";

export default {
    name: "UserCreate",

    data : function() {
        return {
            name: null,
            email: null,
            phone: null,
            password: null,
            confirm_password: null
        }
    },
    methods: {
        addUser: function () {
            if (!this.checkForm())
                return;

            axios.post(state.urlAdminApi + '/add-user', {
                    name: this.name,
                    email: this.email,
                    phone: this.phone,
                    password: this.password,
                    confirm_password: this.confirm_password
                },
                {
                    headers: {
                        Authorization: state.tokenApi,
                    }
                })
                .then((res) => {
                    this.$toast.success('Новый пользователь успешно создан', {
                        position: "top-right",
                        timeout: 5000,
                        closeOnClick: true,
                        pauseOnFocusLoss: true,
                        pauseOnHover: true,
                        draggable: true,
                        draggablePercent: 0.6,
                    showCloseButtonOnHover: false,
                    hideProgressBar: true,
                    closeButton: "button",
                    icon: true,
                    rtl: false
                })
                this.$router.push({ name: 'UserEdit', params: { id: res.data}})
            })
            .catch((err) => {
                console.log(err);

                if (err.response) {
                    if (Array.isArray(err.response.data)) {
                        for (let item in err.response.data) {
                            this.showError(err.response.data[item]);
                        }
                    } else {
                        this.showError(err.response.data.error.message);
                        if (err.response.data.error.advanced) {
                            for (const error of err.response.data.error.advanced) {
                                for (let item in error) {
                                    this.showError(error[item]);
                                }
                            }
                        }
                    }
                }
            });

        },
        checkForm: function () {
            this.errors = [];

            if (!this.name) {
                this.showError('Укажите имя.');
                this.errors.push('Укажите имя.');
            }
            if (!this.email) {
                this.showError('Укажите электронную почту.');
                this.errors.push('Укажите электронную почту.');
            } else if (!this.validEmail(this.email)) {
                this.showError('Укажите корректный адрес электронной почты.');
                this.errors.push('Укажите корректный адрес электронной почты.');
            }
            if(!this.password){
                this.showError('создайте пароль');
                this.errors.push('создайте пароль')
            }
            if(!this.confirm_password){
                this.showError('подтвердите пароль');
                this.errors.push('подтвердите пароль')
            }
            else if (this.confirm_password !== this.password){
                this.showError('подтвердите введенный пароль');
                this.errors.push('подтвердите введенный пароль')
            }

            if (!this.errors.length) {
                return true;
            }
        },
        validEmail: function (email) {
            let re = /^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/
            return re.test(email);
        },
        showError: function (err) {

            this.$toast.error(err, {
                position: "top-right",
                timeout: 5000,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                draggablePercent: 0.6,
                showCloseButtonOnHover: false,
                hideProgressBar: true,
                closeButton: "button",
                icon: true,
                rtl: false
            });
        }
    }
}
</script>

<style scoped>

</style>
