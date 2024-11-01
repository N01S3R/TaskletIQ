<?php App\Helpers\Template::partials('header_admin'); ?>

<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container">
        <header class="d-flex justify-content-between align-items-center m-4">
            <h3 class="p-2 mb-0">{{ pageTitle }}</h3>
            <div class="d-flex">
                <button type="button" class="btn btn-success me-2" @click="openAddUserModal">
                    <i class="bi bi-plus-circle"></i> Dodaj Usera
                </button>
            </div>
        </header>

        <!-- Tabela użytkowników -->
        <div class="table-responsive" id="scrollableTable">
            <table id="userTable" class="table table-striped table-bordered admin-user-table mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Avatar</th>
                        <th>Nazwa</th>
                        <th>Adres email</th>
                        <th>Login</th>
                        <th>Rejestracja</th>
                        <th>Logged In</th>
                        <th>Rola</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
            </table>
        </div>


        <!-- Modal for Adding User -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Dodaj Użytkownika</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="addUser">
                            <div class="mb-3">
                                <label for="name" class="form-label">Imię</label>
                                <input type="text" class="form-control" id="name" v-model="newUser.name" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Nazwa Użytkownika</label>
                                <input type="text" class="form-control" id="username" v-model="newUser.username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" v-model="newUser.email" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Rola</label>
                                <select class="form-control" id="role" v-model="newUser.role">
                                    <option value="operator">Wykonawca</option>
                                    <option value="admin">Administrator</option>
                                    <option value="creator">Twórca</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <div class="text-center">
                                    <img :src="`/images/${newUser.role}.png`" alt="Avatar" width="100" v-if="newUser.role" />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Dodaj</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Editing User -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edytuj Użytkownika</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateUser">
                            <input type="hidden" v-model="editUser.userId">
                            <div class="mb-3">
                                <label for="editName" class="form-label">Imię</label>
                                <input type="text" class="form-control" id="editName" v-model="editUser.username" required>
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" v-model="editUser.email" required>
                            </div>
                            <div class="mb-3">
                                <label for="editLogin" class="form-label">Login</label>
                                <input type="text" class="form-control" id="editLogin" v-model="editUser.login" required>
                            </div>
                            <div class="mb-3">
                                <label for="editRole" class="form-label">Rola</label>
                                <select class="form-control" id="editRole" v-model="editUser.role">
                                    <option value="operator">Wykonawca</option>
                                    <option value="admin">Administrator</option>
                                    <option value="creator">Twórca</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="avatar" class="form-label">Avatar</label>
                                <div class="text-center">
                                    <img :src="`/images/${editUser.role}.png`" alt="Avatar" width="100" v-if="editUser.role" />
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Zapisz Zmiany</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Deleting User -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Usuń Użytkownika</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-body">
                            <p>Czy na pewno chcesz usunąć użytkownika {{ userToDelete }}?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="deleteUserAction">Usuń</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Vue.js and Axios -->
<script src="https://cdn.jsdelivr.net/npm/vue@3.2.37/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: "<?= htmlspecialchars($data['pageTitle']) ?>",
                users: [],
                userToDelete: '',
                newUser: {
                    name: '',
                    email: '',
                    username: '',
                    avatar: '',
                    role: ''
                },
                editUser: {
                    userId: '',
                    username: '',
                    email: '',
                    login: '',
                    avatar: '',
                    logged: false,
                    role: ''
                },
                deleteUser: null,
                dataTable: null,
                loading: true,
                addPasswordFieldType: 'password',
                addPasswordFieldIcon: 'bi bi-eye-slash'
            }
        },
        mounted() {
            this.initializeDataTable();
            this.fetchUsers();

            setTimeout(() => {
                this.loading = false;
            }, 3000);
        },
        watch: {
            'newUser.role': function(newRole) {
                this.updateAvatar(newRole);
            },
            'editUser.role': function(newRole) {
                this.updateAvatar(newRole);
            }
        },
        methods: {
            initializeDataTable() {
                this.dataTable = $('#userTable').DataTable({
                    drawCallback: () => {
                        this.addEventListeners();
                    }
                });
            },
            fetchUsers() {
                axios.get('/api/users')
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.users = response.data.users;
                            this.updateDataTable();
                        } else {
                            this.showNotification('error', response.data.message);
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        this.showNotification('error', 'Błąd przy pobieraniu danych użytkowników.');
                    });
            },
            updateDataTable() {

                const currentPage = this.dataTable.page();
                this.dataTable.clear();

                this.users.forEach(user => {
                    const status = user.logged ?
                        '<span class="badge text-bg-success">Online</span>' :
                        '<span class="badge text-bg-danger">Offline</span>';

                    let roleClass;
                    switch (user.role) {
                        case 'operator':
                            roleClass = 'text-bg-primary';
                            break;
                        case 'creator':
                            roleClass = 'text-bg-secondary';
                            break;
                        case 'admin':
                            roleClass = 'text-bg-warning';
                            break;
                        default:
                            roleClass = 'text-bg-dark';
                    }

                    this.dataTable.row.add([
                        `<div class="text-center">${user.userId}</div>`,
                        `<div class="text-center"><img src="/images/${user.avatar}" alt="Avatar" width="40"></div>`,
                        `<div class="text-center align-middle">${user.username}</div>`,
                        `<div class="text-center align-middle">${user.email}</div>`,
                        `<div class="text-center align-middle">${user.login}</div>`,
                        `<div class="text-center align-middle">${user.registrationDate}</div>`,
                        `<div class="text-center align-middle">${status}</div>`,
                        `<div class="text-center align-middle"><span class="badge ${roleClass}">${user.role}</span></div>`,
                        `<div class="text-center align-middle">
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-info btn-sm edit-user me-2" data-id="${user.userId}">Edytuj</button>
                            <button class="btn btn-danger btn-sm delete-user" data-id="${user.userId}">Usuń</button>
                        </div>
                    </div>`
                    ]);
                });

                this.dataTable.draw(false);
                this.dataTable.page(currentPage).draw('page');
                this.addEventListeners();
            },
            addEventListeners() {
                const editButtons = document.querySelectorAll('.edit-user');
                const deleteButtons = document.querySelectorAll('.delete-user');

                editButtons.forEach(button => {
                    button.addEventListener('click', (event) => {
                        const userId = event.target.getAttribute('data-id');
                        const user = this.users.find(u => u.userId == userId);
                        this.openEditUserModal(user);
                    });
                });

                deleteButtons.forEach(button => {
                    button.addEventListener('click', (event) => {
                        const userId = event.target.getAttribute('data-id');
                        this.openDeleteUserModal(userId);
                    });
                });
            },
            openAddUserModal() {
                $('#addUserModal').modal('show');
            },
            updateAvatar(role) {
                this.newUser.avatar = `${role}.png`;
                if (this.editUser.role) {
                    this.editUser.avatar = `${role}.png`;
                }
            },
            addUser() {
                const userData = {
                    user_name: this.newUser.name,
                    user_email: this.newUser.email,
                    user_login: this.newUser.username,
                    user_avatar: this.newUser.avatar,
                    user_role: this.newUser.role
                };

                axios.post('/api/user/add', userData)
                    .then(response => {
                        if (response.data.status === 'success') {
                            $('#addUserModal').modal('hide');
                            this.newUser = {
                                name: '',
                                email: '',
                                username: '',
                                avatar: '',
                                role: 'operator'
                            };
                            this.fetchUsers();
                            this.showNotification('success', response.data.message);
                        } else {
                            this.showNotification('error', response.data.message);
                        }
                    })
                    .catch(error => {
                        this.showNotification('error', 'Wystąpił błąd podczas dodawania użytkownika.');
                    });
            },
            openEditUserModal(user) {
                this.editUser = {
                    userId: user.userId,
                    username: user.username,
                    email: user.email,
                    login: user.login,
                    avatar: user.avatar,
                    role: user.role
                };
                this.updateAvatar(user.role);
                $('#editUserModal').modal('show');
            },
            updateUser() {
                axios.put(`/api/user/update/${this.editUser.userId}`, this.editUser)
                    .then(response => {
                        const updatedUser = response.data.data;
                        this.fetchUsers();
                        $('#editUserModal').modal('hide');
                    })
                    .catch(error => {
                        this.showNotification('error', 'Błąd edycji użytkownika');
                    });
            },
            openDeleteUserModal(userId) {
                const user = this.users.find(u => u.userId == userId);
                this.userToDelete = user ? user.username : '';
                this.deleteUser = userId;
                $('#deleteUserModal').modal('show');
            },
            deleteUserAction() {
                if (this.deleteUser) {
                    axios.delete('/api/user/delete/' + this.deleteUser)
                        .then(response => {
                            const data = response.data;
                            if (data.success) {
                                this.fetchUsers();
                                $('#deleteUserModal').modal('hide');
                                this.showNotification('success', data.message);
                            } else {
                                this.showNotification('error', data.message);
                            }
                        })
                        .catch(error => {
                            this.showNotification('error', 'Błąd usunięcia użytkownika');
                        });
                }
            },
            showNotification(type, message) {
                Lobibox.notify(type, {
                    msg: message,
                    icon: type === 'success' ? 'bi bi-check-circle' : 'bi bi-exclamation-triangle',
                    title: type === 'success' ? 'Sukces' : 'Błąd',
                    sound: false,
                    position: 'top right',
                });
            }
        }
    }).mount('#app');
</script>

</body>

</html>