<?php App\Helpers\Template::partials('header_creator'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container my-4">
        <nav aria-label="breadcrumb" class="main-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Creator</a></li>
                <li class="breadcrumb-item"><a href="#">{{ pageTitle }}</a></li>
            </ol>
        </nav>
        <header class="d-flex justify-content-end align-items-center m-4">
            <div class="d-flex">
                <a href="/creator/dashboard" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Pulpit</a>
            </div>
        </header>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg mb-4">
                    <div class="card-header p-3">
                        <h5>Token</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label for="token" class="form-label">Twój token</label>
                            <input type="text" class="form-control" id="token" name="token" v-model="token" disabled>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end align-items-center p-3">
                        <button type="button" class="btn btn-primary" @click="generateToken">Generuj token</button>
                    </div>
                </div>
                <div class="card shadow-lg">
                    <div class="card-header p-3 d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Wygenerowane Linki ({{ links.length }})</h5>
                        <button type="button" class="btn btn-success" @click="refreshLinks"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                    <div v-if="load" class="text-center py-2">
                        <i class="bi bi-hourglass-split"></i> Trwa ładowanie...
                    </div>
                    <div class="card-body" v-if="!load">
                        <div class="d-flex justify-content-end align-items-center mb-3">
                            <label for="token" class="form-label mb-0">Kończy ważność</label>
                        </div>
                        <div ref="linksList" class="custom-row-links p-3" style="position: relative;">
                            <div v-for="(link, index) in links" :key="link.token_id" class="input-group mb-3">
                                <span class="input-group-text">{{ index + 1 }}</span>
                                <input type="hidden" v-model="link.token_id">
                                <input type="text" class="form-control" :value="link.token" readonly>
                                <button class="btn btn-primary" type="button" @click="copyLink(link.token)"><i class="bi bi-clipboard"></i></button>
                                <span class="input-group-text">{{ formatDateTime(link.expiration) }}</span>
                                <button class="btn btn-danger" type="button" @click="confirmDelete(index)"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Nowa kolumna z użytkownikami -->
            <div class="col-md-3">
                <div class="card shadow-lg">
                    <div class="card-header p-3">
                        <h5 class="text-center">Zaproszeni Użytkownicy ({{ users.length }})</h5>
                    </div>
                    <div class="card-body custom-row-code" ref="usersList" style="position: relative;">
                        <div>
                            <div v-for="(user, index) in users" :key="user.user_id" class="card m-lg-2 text-center border-warning">
                                <div class="card-body d-flex align-items-center justify-content-center">
                                    <img :src="'/images/' + user.user_avatar" alt="Avatar" class="img-fluid rounded-circle ms-3 pe-1" width="40">
                                    <h5 class="card-title">{{ user.user_login }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal potwierdzający usunięcie tokena -->
    <div class="modal fade" id="deleteTokenModal" tabindex="-1" aria-labelledby="deleteTokenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTokenModalLabel">Potwierdzenie usunięcia tokena</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Czy na pewno chcesz usunąć ten token?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="deleteToken">Usuń</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    Vue.createApp({
        data() {
            return {
                pageTitle: <?= json_encode($data['pageTitle']); ?>,
                loading: true,
                token: '<?= $data["token"] ?>',
                users: <?= json_encode($data['users']); ?>,
                baseUrl: <?= json_encode($data['baseUrl']); ?>,
                links: [],
                load: false,
                deleteIndex: null,
                csrfToken: <?php echo json_encode($data['csrfToken']); ?>,
            };
        },
        methods: {
            initializeScrollbar() {
                this.$nextTick(() => {
                    const usersList = this.$refs.usersList;
                    const linksList = this.$refs.linksList;
                    if (usersList) {
                        new PerfectScrollbar(usersList);
                    } else {
                        console.error('Element usersList nie został znaleziony');
                    }
                    if (linksList) {
                        new PerfectScrollbar(linksList);
                    } else {
                        console.error('Element linksList nie został znaleziony');
                    }
                });
            },
            generateToken() {
                const url = '/api/code/' + this.token;

                axios.post(url, {
                        csrf_token: this.csrfToken
                    })
                    .then(response => {
                        if (response.data.success) {
                            const newToken = {
                                token_id: response.data.token_id,
                                token: this.baseUrl + response.data.token,
                                expiration: response.data.expiration,
                                csrf_token: this.csrfToken,
                            };
                            this.links.push(newToken);
                            this.showNotification('success', 'Token został wygenerowany pomyślnie.');
                        } else {
                            this.showNotification('error', response.data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Wystąpił błąd:', error);
                        this.showNotification('error', 'Wystąpił błąd podczas wysyłania żądania.');
                    });
            },
            fetchLinks() {
                axios.get('/api/links')
                    .then(response => {
                        if (response.data.links) {
                            this.links = response.data.links.map(link => ({
                                ...link,
                                token: `${this.baseUrl}register/${link.token}`
                            }));
                        } else {
                            console.error('Nie udało się pobrać linków');
                        }
                    })
                    .catch(error => {
                        console.error('Błąd:', error);
                    });
            },
            copyLink(url) {
                const tempInput = document.createElement('input');
                tempInput.value = url;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                this.showNotification('info', 'Link skopiowany: ' + url);
            },
            formatDateTime(dateString) {
                const date = new Date(dateString);
                const options = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                return date.toLocaleString('pl-PL', options);
            },
            confirmDelete(index) {
                this.deleteIndex = index;
                const myModal = new bootstrap.Modal(document.getElementById('deleteTokenModal'), {
                    keyboard: false
                });
                myModal.show();
            },
            deleteToken() {
                if (this.deleteIndex !== null && this.deleteIndex >= 0 && this.deleteIndex < this.links.length) {
                    const link = this.links[this.deleteIndex];
                    const linkId = link ? link.token_id : undefined;
                    if (linkId) {
                        const url = '/api/token/delete/' + linkId;

                        axios.delete(url, {
                                data: {
                                    csrf_token: this.csrfToken
                                }
                            })
                            .then(response => {
                                this.links.splice(this.deleteIndex, 1);
                                this.deleteIndex = null;
                                this.showNotification('success', response.data.message);
                            })
                            .catch(error => {
                                console.error('Wystąpił błąd:', error);
                                this.showNotification('error', 'Wystąpił błąd podczas wysyłania żądania.');
                            });
                    } else {
                        console.error('Nie znaleziono id linku do usunięcia.');
                    }
                } else {
                    console.error("Nieprawidłowy indeks linku do usunięcia.");
                }
            },
            refreshLinks() {
                this.load = true;
                this.fetchLinks();
                setTimeout(() => {
                    this.load = false;
                    this.initializeScrollbar();
                }, 1500);
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
            }
        },
        mounted() {
            this.fetchLinks();
            setTimeout(() => {
                this.loading = false;
                this.initializeScrollbar();
            }, 1000);
        }
    }).mount('#app');
</script>
</body>

</html>