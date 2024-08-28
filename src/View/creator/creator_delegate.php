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
            <div>
                <button type="button" class="btn btn-secondary me-2" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="<strong>Jak przypisać użytkownika do zadania:</strong><ul class='text-start list-group list-group-numbered'><li class='list-group-item'>Zaznacz zadanie.</li><li class='list-group-item'>Zaznacz użytkownika.</li><li class='list-group-item'>Kliknij <em>+ Przypisz</em>.</li></ul>">
                    Pomoc
                </button>

                <a href="/creator/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Pulpit
                </a>
            </div>
        </header>
        <div class="row justify-content-center">
            <div class="col-12 col-md-9">
                <div class="card">
                    <div class="card-header p-3">
                        <h5 class="text-center">Projekty ({{ userProjects.length }})</h5>
                    </div>
                    <div class="card-body" ref="projectList" style="position: relative;">
                        <div class="custom-row-delegate">
                            <div v-for="(project, projectIndex) in userProjects" :key="'project-' + projectIndex">
                                <div class="col-12 col-xl-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">{{ projectIndex + 1 }} Projekt: {{ project.project_name }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div v-for="(task, taskIndex) in project.tasks" :key="'task-' + taskIndex">
                                    <div class="row my-2 mx-1">
                                        <div class="col-sm-12 col-md-4 col-lg-9 col-xl-4 my-2">
                                            <div class="card border-warning" @click="selectTask(task.task_id)" :class="{ 'selected': selectedTaskId === task.task_id }" style="cursor: pointer;">
                                                <div class="card-body">
                                                    <h5 class="card-title">Zadanie: {{ task.task_name }}</h5>
                                                    <p class="card-text">{{ task.task_created_at }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-8 col-lg-9 col-xl-8">
                                            <div class="card border-danger">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-4" v-for="(column, columnIndex) in Math.ceil(task.users.length / 4)" :key="'column-' + columnIndex">
                                                            <div v-for="(user, userIndex) in task.users.slice(columnIndex * 4, (columnIndex + 1) * 4)" :key="'user-' + userIndex">
                                                                <div class="d-flex align-items-center highlight-on-hover justify-content-center py-2" :data-id="user.user_id" @click="confirmAndUnassign(user.user_id, task.task_id)" style="cursor: pointer;">
                                                                    <img :src="'/images/' + user.user_avatar" class="avatar-img" alt="User Avatar" style="height: 30px; width: 30px; margin-right: 5px;">
                                                                    <span>{{ user.user_login }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div v-if="task.users.length > 11" class="alert alert-danger d-flex justify-content-center align-items-center mb-0 mt-2" role="alert">MAX</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prawa kolumna - Użytkownicy (desktop) -->
            <div class="col-md-3 d-none d-lg-block">
                <div class="card">
                    <div class="card-header p-3">
                        <h5 class="text-center">Użytkownicy ({{ users.length }})</h5>
                    </div>
                    <div class="card-body" ref="userList" style="position: relative;">
                        <div class="custom-row-delegate-right">
                            <div v-for="(user, userIndex) in users" :key="'user-' + userIndex" class="card m-lg-2 border-warning" @click="selectUser(user.user_id)" :class="{ 'selected': selectedUserId === user.user_id }" style="cursor: pointer;">
                                <div class="card-body d-flex align-items-center">
                                    <img :src="'/images/' + user.user_avatar" class="card-img-top" alt="User Avatar" style="height: 10%; width: 10%; margin-right: 10px;">
                                    <div>
                                        <h5 class="card-title">{{ user.user_login }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-3 d-none d-md-block">
                <button @click.stop="confirmSelection" class="btn btn-primary floating-btn">
                    <i class="bi bi-plus-lg"></i> Przypisz
                </button>
            </div>
        </div>
    </div>

    <!-- Modal potwierdzający usunięcie przypisania -->
    <div class="modal fade" id="confirmUnassignModal" tabindex="-1" aria-labelledby="confirmUnassignModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmUnassignModalLabel">Potwierdzenie usunięcia przypisania</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Czy na pewno chcesz usunąć tego użytkownika z zadania?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                    <button type="button" class="btn btn-danger" @click="confirmUnassign" data-bs-dismiss="modal">Usuń</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas użytkowników (mobile) -->
    <div class="offcanvas offcanvas-end d-md-none" tabindex="-1" id="offcanvasUsers" aria-labelledby="offcanvasUsersLabel">
        <div class="offcanvas-header">
            <h5 id="offcanvasUsersLabel">Użytkownicy ({{ users.length }}) </h5>
            <button @click.stop="confirmSelection" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Przypisz
            </button>
            <button type="button" class="btn-close" @click="closeOffcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div v-for="(user, userIndex) in users" :key="'offcanvas-user-' + userIndex" class="card m-lg-2" @click="selectUser(user.user_id)" :class="{ 'selected': selectedUserId === user.user_id }" style="cursor: pointer;">
                <div class="card-body d-flex align-items-center">
                    <img :src="'/images/' + user.user_avatar" class="card-img-top" alt="User Avatar" style="height: 10%; width: 10%; margin-right: 10px;">
                    <div>
                        <h5 class="card-title">{{ user.user_login }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar/dist/perfect-scrollbar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lobibox/js/lobibox.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const app = Vue.createApp({
            data() {
                return {
                    loading: true,
                    selectedUserId: null,
                    selectedTaskId: null,
                    pageTitle: <?php echo json_encode($data['pageTitle']); ?>,
                    userProjects: <?php echo json_encode($data["userProjects"]); ?>,
                    users: <?php echo json_encode($data["users"]); ?>
                };
            },
            methods: {
                initializeScrollbars() {
                    this.$nextTick(() => {
                        const projectList = this.$refs.projectList;
                        const usersList = this.$refs.userList;
                        if (projectList) {
                            new PerfectScrollbar(projectList);
                        } else {
                            console.warn('Project list reference not found.');
                        }
                        if (usersList) {
                            new PerfectScrollbar(usersList);
                        } else {
                            console.warn('Users list reference not found.');
                        }
                    });
                },
                selectUser(userId) {
                    this.selectedUserId = (this.selectedUserId === userId) ? null : userId;
                },
                selectTask(taskId) {
                    if (taskId === 0) {
                        this.showNotification('error', 'Invalid task ID.');
                        return;
                    }
                    this.selectedTaskId = (this.selectedTaskId === taskId) ? null : taskId;

                    // Show offcanvas on mobile
                    if (window.innerWidth < 768 && this.selectedTaskId) {
                        const offcanvasUsers = new bootstrap.Offcanvas(document.getElementById('offcanvasUsers'));
                        offcanvasUsers.show();
                    }
                },
                confirmSelection() {
                    if (this.selectedUserId == null) {
                        this.showNotification('error', 'Nie wybrano użytkownika.');
                    } else if (this.selectedTaskId == null) {
                        this.showNotification('error', 'Nie wybrano zadania.');
                    } else {
                        this.sendData();

                        // Close offcanvas on mobile
                        if (window.innerWidth < 768) {
                            this.closeOffcanvas();
                        }
                    }
                },
                sendData() {
                    const endpoint = '/api/creator/assign';
                    const dataToSend = {
                        userId: this.selectedUserId,
                        taskId: this.selectedTaskId,
                    };

                    axios.post(endpoint, dataToSend)
                        .then(response => {
                            if (response.data && response.data.success) {
                                const newUser = response.data.user;
                                newUser.user_name = newUser.username;
                                this.userProjects.forEach(project => {
                                    project.tasks.forEach(task => {
                                        if (task.task_id === this.selectedTaskId) {
                                            task.users.push(newUser);
                                        }
                                    });
                                });
                                this.clearAssign();
                                this.showNotification('success', response.data.success);
                            } else {
                                this.clearAssign();
                                this.showNotification('error', response.data ? response.data.error : 'Nieprawidłowe dane zwrócone przez serwer.');
                            }
                        })
                        .catch(error => {
                            console.error('Wystąpił błąd podczas przypisywania użytkownika:', error);
                            this.showNotification('error', 'Wystąpił błąd podczas przypisywania użytkownika.');
                        });
                },
                confirmAndUnassign(userId, taskId) {
                    $('#confirmUnassignModal').modal('show');
                    $('#confirmUnassignModal').on('click', '.btn-danger', () => {
                        this.sendUnassignData(userId, taskId);
                        $('#confirmUnassignModal').off('click', '.btn-danger');
                    });
                },
                clearAssign() {
                    this.selectedUserId = null;
                    this.selectedTaskId = null;
                },
                sendUnassignData(userId, taskId) {
                    const unassignEndpoint = '/api/creator/unassign';
                    const unassignData = {
                        userId: userId,
                        taskId: taskId,
                    };

                    fetch(unassignEndpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(unassignData),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.clearAssign();
                                this.showNotification('info', data.success);
                                this.userProjects.forEach(project => {
                                    project.tasks.forEach(task => {
                                        if (task.task_id === taskId) {
                                            task.users = task.users.filter(user => user.user_id !== userId);
                                        }
                                    });
                                });
                            } else {
                                this.clearAssign();
                                this.showNotification('error', data.error);
                            }
                        })
                        .catch(error => {
                            this.showNotification('error', error.message);
                        });
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
                closeOffcanvas() {
                    this.selectedTaskId = null;

                    let offcanvasElement = document.getElementById('offcanvasUsers');
                    if (offcanvasElement) {
                        let bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasElement);
                        if (bsOffcanvas) {
                            bsOffcanvas.hide();
                        }
                    } else {
                        console.warn('Offcanvas element not found.');
                    }
                }
            },
            mounted() {
                setTimeout(() => {
                    this.loading = false;
                    this.initializeScrollbars(); // Initialize scrollbars after component is mounted
                }, 1000);
            }
        }).mount('#app');
    });
</script>
</body>

</html>