<?php App\Helpers\Template::partials('header_operator'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-xl-4 col-lg-5 col-md-8 col-12 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="bi bi-key me-2 fs-4"></i>
                        <h5 class="mb-0">Zmiana hasła</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="changePassword" class="p-2">
                            <div class="mb-2">
                                <label for="currentPassword" class="form-label fw-bold">Aktualne hasło</label>
                                <input type="password" class="form-control form-control-sm rounded-pill shadow-sm" id="currentPassword" v-model="currentPassword" @input="debouncedValidateField('currentPassword')" required>
                                <div v-if="errors.currentPassword" class="text-danger">{{ errors.currentPassword }}</div>
                            </div>
                            <div class="mb-2">
                                <label for="newPassword" class="form-label fw-bold">Nowe hasło</label>
                                <input type="password" class="form-control form-control-sm rounded-pill shadow-sm" id="newPassword" v-model="newPassword" @input="debouncedValidateField('newPassword')" required>
                                <div v-if="errors.newPassword" class="text-danger">{{ errors.newPassword }}</div>
                            </div>
                            <div class="mb-2">
                                <label for="confirmPassword" class="form-label fw-bold">Potwierdź nowe hasło</label>
                                <input type="password" class="form-control form-control-sm rounded-pill shadow-sm" id="confirmPassword" v-model="confirmPassword" @input="debouncedValidateField('confirmPassword')" required>
                                <div v-if="errors.confirmPassword" class="text-danger">{{ errors.confirmPassword }}</div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-pill mt-2">Zmień hasło</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/vue@3.2.31/dist/vue.global.js"></script> <!-- Updated Vue link -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

<script>
    const {
        createApp
    } = Vue;

    const app = createApp({
        data() {
            return {
                pageTitle: <?php echo json_encode($data['pageTitle'] ?? 'Ustawienia użytkownika'); ?>,
                loading: true,
                user: {
                    username: <?php echo json_encode($data['user']['username'] ?? 'exampleUser'); ?>,
                    email: <?php echo json_encode($data['user']['email'] ?? 'user@example.com'); ?>,
                    avatar: '/images/operator.png'
                },
                currentPassword: '',
                newPassword: '',
                confirmPassword: '',
                errors: {
                    currentPassword: '',
                    newPassword: '',
                    confirmPassword: ''
                }
            };
        },
        methods: {
            async validateField(field) {
                try {
                    const response = await axios.post('/api/validate-password-field', {
                        field: field,
                        value: this[field]
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
            changePassword() {
                if (this.newPassword !== this.confirmPassword) {
                    this.showNotification('error', 'Nowe hasła muszą się zgadzać.');
                    return;
                }
                this.validateField('currentPassword');
                this.validateField('newPassword');
                this.validateField('confirmPassword');

                if (!this.errors.currentPassword && !this.errors.newPassword && !this.errors.confirmPassword) {
                    axios.post('/api/change-password', {
                            currentPassword: this.currentPassword,
                            newPassword: this.newPassword
                        })
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showNotification('success', response.data.message);
                                this.currentPassword = '';
                                this.newPassword = '';
                                this.confirmPassword = '';
                            } else {
                                this.showNotification('error', response.data.message);
                            }
                        })
                        .catch(error => {
                            this.showNotification('error', error.response.data);
                        });
                }
            },
            showNotification(type, message) {
                const config = {
                    success: {
                        title: 'Sukces',
                        icon: 'bi bi-check-circle'
                    },
                    error: {
                        title: 'Błąd',
                        icon: 'bi bi-exclamation-triangle'
                    },
                    warning: {
                        title: 'Ostrzeżenie',
                        icon: 'bi bi-exclamation-circle'
                    },
                    info: {
                        title: 'Informacja',
                        icon: 'bi bi-info-circle'
                    },
                };

                const {
                    title = 'Powiadomienie', icon = 'bi bi-bell'
                } = config[type] || {};

                Lobibox.notify(type, {
                    msg: message,
                    icon: icon,
                    title: title,
                    sound: false,
                    position: 'top right',
                });
            },
        },
        mounted() {
            this.loading = false;
        }
    });

    app.mount('#app');
</script>

</body>

</html>