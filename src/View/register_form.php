<?php App\Helpers\Template::partials('header_guest'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div v-else>
        <div class="container mt-4">
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
                                    <div v-if="errors.username" class="text-danger">{{ errors.username }}</div>
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

    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true" ref="registerModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Trwa rejestracja...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" :style="{ width: progressBarWidth + '%' }" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-center mt-2">
                        <span>{{ progressStage }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3.2.31/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                form: {
                    fullName: '',
                    email: '',
                    username: '',
                    registration_code: <?php echo json_encode($data['registrationCode']); ?>,
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
                message: '',
                progressBarWidth: 0,
                progressStage: 'Rozpoczynanie rejestracji...',
            };
        },
        computed: {
            isFormValid() {
                return !this.errors.fullName && !this.errors.email && !this.errors.username && !this.errors.password;
            }
        },
        methods: {
            async validateField(field) {
                try {
                    const response = await axios.post('/api/validate-field', {
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
                this.progressBarWidth = 0;
                this.progressStage = 'Rozpoczynanie rejestracji...';

                await this.validateField('fullName');
                await this.validateField('email');
                await this.validateField('username');
                await this.validateField('password');

                if (this.isFormValid) {
                    this.showModal();
                    let progress = 0;
                    const progressInterval = setInterval(() => {
                        if (progress < 40) {
                            progress += 10;
                            this.progressBarWidth = progress;
                            this.progressStage = 'Sprawdzanie danych...';
                        } else if (progress >= 40 && progress < 70) {
                            progress += 10;
                            this.progressBarWidth = progress;
                            this.progressStage = 'Przygotowanie formularza...';
                        } else if (progress >= 70 && progress < 100) {
                            progress += 10;
                            this.progressBarWidth = progress;
                            this.progressStage = 'Wysyłanie formularza...';
                        } else if (progress === 100) {
                            clearInterval(progressInterval);
                            this.progressStage = 'Rejestracja zakończona!';
                            this.closeModal();
                        }
                    }, 500);

                    try {
                        const response = await axios.post('/register', this.form);
                        if (response && response.data) {
                            this.message = response.data.message;
                            this.error = '';
                            this.form.fullName = '';
                            this.form.email = '';
                            this.form.username = '';
                            this.form.registration_code = '';
                            this.form.password = '';
                        } else {
                            throw new Error('Nieoczekiwana odpowiedź serwera');
                        }
                    } catch (error) {
                        this.error = error.response && error.response.data ? error.response.data.error : 'Rejestracja nie powiodła się';
                        this.message = '';
                        this.closeModal();
                    }
                }
            },

            showModal() {
                const modal = new bootstrap.Modal(this.$refs.registerModal);
                modal.show();
            },
            closeModal() {
                const modal = bootstrap.Modal.getInstance(this.$refs.registerModal);
                if (modal) modal.hide();
            }
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
            }, 1000);
        }
    }).mount('#app');
</script>
</body>

</html>