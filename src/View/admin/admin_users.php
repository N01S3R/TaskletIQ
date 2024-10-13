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
        <div class="table-responsive">
            <table id="userTable" class="table table-striped table-bordered admin-user-table mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>ID</th>
                        <th>Avatar</th>
                        <th>Nazwa</th>
                        <th>Email</th>
                        <th>Login</th>
                        <th>Rejestracja</th>
                        <th>Logged In</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.userId">
                        <td class="text-center align-middle">{{ user.userId }}</td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center">
                                <img :src="'/images/' + user.avatar" alt="avatar" width="50">
                            </div>
                        </td>
                        <td class="text-center align-middle">{{ user.username }}</td>
                        <td class="text-center align-middle">{{ user.email }}</td>
                        <td class="text-center align-middle">{{ user.login }}</td>
                        <td class="text-center align-middle">{{ user.registrationDate }}</td>
                        <td class="text-center align-middle">{{ user.logged ? 'Yes' : 'No' }}</td>
                        <td class="text-center align-middle">{{ user.role }}</td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center">
                                <button @click="openEditUserModal(user)" class="btn btn-primary btn-sm me-1">Edit</button>
                                <button @click="openDeleteUserModal(user.userId)" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal for Adding User -->
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="addUser">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" v-model="newUser.name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" v-model="newUser.email" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" v-model="newUser.username" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Editing User -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="updateUser">
                            <input type="hidden" v-model="editUser.userId">
                            <div class="mb-3">
                                <label for="editName" class="form-label">Name</label>
                                <input type="text" class="form-control" id="editName" v-model="editUser.username" required>
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="editEmail" v-model="editUser.email" required>
                            </div>
                            <div class="mb-3">
                                <label for="editUsername" class="form-label">Username</label>
                                <input type="text" class="form-control" id="editUsername" v-model="editUser.login" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Deleting User -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this user?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                users: <?= json_encode($data['users']) ?>,
                newUser: {
                    name: '',
                    email: '',
                    username: '',
                    password: '',
                    avatar: '',
                    registrationDate: '',
                    logged: false,
                    role: ''
                },
                editUser: {
                    userId: '',
                    username: '',
                    email: '',
                    login: '',
                    avatar: '',
                    registrationDate: '',
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

            setTimeout(() => {
                this.loading = false;
            }, 3000);
        },
        methods: {
            initializeDataTable() {
                this.dataTable = $('#userTable').DataTable({});
            },
            openAddUserModal() {
                $('#addUserModal').modal('show');
            },
            addUser() {
                axios.post('/api/users/add', this.newUser)
                    .then(response => {
                        this.users.push(response.data);
                        $('#addUserModal').modal('hide');
                        this.newUser = {
                            name: '',
                            email: '',
                            username: '',
                            password: ''
                        };
                    })
                    .catch(error => {
                        console.error('Błąd podczas dodawania użytkownika', error);
                    });
            },
            openEditUserModal(user) {
                this.editUser = {
                    userId: user.userId,
                    username: user.username,
                    email: user.email,
                    login: user.login,
                };
                $('#editUserModal').modal('show');
            },
            updateUser() {
                axios.put(`/api/user/update/${this.editUser.userId}`, this.editUser)
                    .then(response => {
                        const updatedUser = response.data.data;
                        const index = this.users.findIndex(u => u.userId === this.editUser.userId);
                        if (index !== -1) {
                            this.$set(this.users, index, updatedUser);
                            $('#editUserModal').modal('hide');
                        }
                    })
                    .catch(error => {
                        this.showNotification('error', 'Błąd edycji użytkownika');
                    });
            },
            openDeleteUserModal(userId) {
                this.deleteUser = userId;
                $('#deleteUserModal').modal('show');
            },
            deleteUserAction() {
                if (this.deleteUser) {
                    axios.delete('/api/user/delete/' + this.deleteUser)
                        .then(response => {
                            const data = response.data;
                            if (data.success) {
                                this.users = this.users.filter(u => u.userId !== this.deleteUser);
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
            },
        }
    }).mount('#app');
</script>

</body>

</html>