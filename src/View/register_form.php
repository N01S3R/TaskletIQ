<?php App\Helpers\Template::partials('header_guest'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div v-else>
        <div class="container mt-4" id="app">
            <div class="row mt-4">
                <div class="col-md-4 offset-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Sign Up</h5>
                            <div v-if="message" class="mt-3 alert alert-success">{{ message }}</div>
                            <div v-if="error" class="mt-3 alert alert-danger">{{ error }}</div>
                            <form @submit.prevent="register" ref="registrationForm">
                                <div class="mb-3">
                                    <label for="fullName" class="form-label">Imię:</label>
                                    <input type="text" class="form-control" id="fullName" v-model="form.fullName" @input="debouncedValidateField('fullName')" required autocomplete="off">
                                    <div v-if="errors.fullName" class="text-danger">{{ errors.fullName }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Adres email:</label>
                                    <input type="email" class="form-control" id="email" v-model="form.email" @input="debouncedValidateField('email')" required autocomplete="off">
                                    <div v-if="errors.email" class="text-danger">{{ errors.email }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="newUsername" class="form-label">Login:</label>
                                    <input type="text" class="form-control" id="newUsername" v-model="form.username" @input="debouncedValidateField('username')" required autocomplete="off">
                                    <div v-if="errors.username" class="text-danger">{{ errors.user_login }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="registration_code" class="form-label">Kod rejestracyjny (opcjonalnie):</label>
                                    <input type="text" class="form-control" v-model="form.registration_code" id="registration_code" autocomplete="off">
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password:</label>
                                    <input type="password" class="form-control" id="password" v-model="form.password" @input="debouncedValidateField('password')" required autocomplete="off">
                                    <div v-if="errors.password" class="text-danger">{{ errors.password }}</div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="submit" class="btn btn-success" :disabled="!isFormValid"><i class="bi bi-person-plus"></i> Zarejestruj</button>
                                    <a href="/login" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right"></i> Logowanie
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    new Vue({
        el: '#app',
        data: {
            form: {
                fullName: '',
                email: '',
                username: '',
                registration_code: '',
                password: ''
            },
            errors: {
                fullName: '',
                email: '',
                username: '',
                password: ''
            },
            loading: true,
            error: '',
            message: ''
        },
        computed: {
            isFormValid() {
                return !this.errors.fullName && !this.errors.email && !this.errors.username && !this.errors.password;
            }
        },
        methods: {
            async validateField(field) {
                try {
                    const response = await axios.post('/validate-field', {
                        field: field,
                        value: this.form[field]
                    });
                    if (!response.data.valid) {
                        this.errors[field] = response.data.message;
                    } else {
                        this.errors[field] = '';
                    }
                } catch (error) {
                    this.errors[field] = 'Błąd walidacji pola';
                }
            },
            debouncedValidateField: _.debounce(function(field) {
                this.validateField(field);
            }, 500),
            async register() {
                this.error = '';
                this.message = '';

                await this.validateField('fullName');
                await this.validateField('email');
                await this.validateField('username');
                await this.validateField('password');

                if (this.isFormValid) {
                    try {
                        const response = await axios.post('/register', this.form);

                        if (response && response.data) {
                            this.message = response.data.message;
                            this.error = '';

                            // Resetowanie formularza
                            this.form.fullName = '';
                            this.form.email = '';
                            this.form.username = '';
                            this.form.registration_code = '';
                            this.form.password = '';

                            console.log(response.data.message);
                        } else {
                            throw new Error('Nieoczekiwana odpowiedź serwera');
                        }
                    } catch (error) {
                        console.error(error);
                        this.error = error.response && error.response.data ? error.response.data.error : 'Rejestracja nie powiodła się';
                        this.message = '';
                    }
                }
            }
        }
    });
</script>
</body>

</html>