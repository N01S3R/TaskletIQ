<?php App\Helpers\Template::partials('header_admin'); ?>

<!-- <?php
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        ?> -->

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
        <table id="userTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.user_id">
                    <td>{{ user.user_id }}</td>
                    <td>{{ user.user_login }}</td>
                    <td>{{ user.user_email }}</td>
                    <td>{{ user.user_name }}</td>
                    <td>
                        <button @click="openEditUserModal(user)" class="btn btn-primary btn-sm">Edit</button>
                        <button @click="openDeleteUserModal(user.user_id)" class="btn btn-danger btn-sm">Delete</button>
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
                    <!-- Form for adding user -->
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
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" v-model="newUser.password" required>
                                <button class="btn btn-outline-secondary" type="button" @click="toggleAddPasswordVisibility">
                                    <i :class="addPasswordFieldIcon"></i>
                                </button>
                            </div>
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
                    <!-- Form for editing user -->
                    <form @submit.prevent="updateUser">
                        <input type="hidden" v-model="editUser.user_id">
                        <div class="mb-3">
                            <label for="editName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="editName" v-model="editUser.user_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" v-model="editUser.user_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" v-model="editUser.user_login" required>
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Vue.js and Axios -->
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    new Vue({
        el: '#app',
        data: {
            pageTitle: "<?= htmlspecialchars($data['pageTitle']) ?>",
            users: <?= json_encode($data['users']) ?>,
            newUser: {
                name: '',
                email: '',
                username: '',
                password: ''
            },
            editUser: {
                user_id: '',
                user_name: '',
                user_email: '',
                user_login: '',
            },
            deleteUser: null,
            dataTable: null,
            loading: true,
            passwordFieldType: 'password',
            passwordFieldIcon: 'bi bi-eye-slash',
            addPasswordFieldType: 'password',
            addPasswordFieldIcon: 'bi bi-eye-slash'
        },
        mounted() {
            this.initializeDataTable();
            setTimeout(() => {
                this.loading = false;
            }, 3000);
        },
        methods: {
            initializeDataTable() {
                this.dataTable = $('#userTable').DataTable({

                });
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
                    user_id: user.user_id,
                    user_name: user.user_name,
                    user_email: user.user_email,
                    user_login: user.user_login,
                };
                $('#editUserModal').modal('show');
            },
            updateUser() {
                axios.put(`/api/user/update/${this.editUser.user_id}`, this.editUser)
                    .then(response => {
                        const updatedUser = response.data.data;
                        const index = this.users.findIndex(u => u.user_id === this.editUser.user_id);
                        if (index !== -1) {
                            this.$set(this.users, index, updatedUser); // Zaktualizuj użytkownika w stanie Vue.js
                            $('#editUserModal').modal('hide'); // Zamknij modal po sukcesie
                            this.showNotification('success', 'User updated successfully');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating user', error);
                        this.showNotification('error', 'Error updating user');
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
                                this.users = this.users.filter(u => u.user_id !== this.deleteUser);
                                $('#deleteUserModal').modal('hide');
                                this.showNotification('success', data.message);
                            } else {
                                this.showNotification('error', data.message);
                            }
                        })
                        .catch(error => {
                            const errorMessage = error.response ? error.response.data.message : 'Wystąpił błąd';
                            this.showNotification('error', errorMessage);
                        });
                } else {
                    console.error("Nie wybrano projektu do usunięcia.");
                }
            },
            togglePasswordVisibility() {
                this.passwordFieldType = this.passwordFieldType === 'password' ? 'text' : 'password';
                this.passwordFieldIcon = this.passwordFieldType === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
            },
            toggleAddPasswordVisibility() {
                this.addPasswordFieldType = this.addPasswordFieldType === 'password' ? 'text' : 'password';
                this.addPasswordFieldIcon = this.addPasswordFieldType === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
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
        }
    });
</script>

</body>

</html>