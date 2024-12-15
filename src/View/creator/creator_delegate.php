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
                <button @click="startTutorial" class="btn btn-info me-2">Rozpocznij tutorial</button>
                <button @click="resetTutorial" class="btn btn-warning">Resetuj tutorial</button>
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
                            <div v-if="userProjects.length === 0" class="text-center">
                                <p>Nie masz żadnych projektów.</p>
                            </div>

                            <div v-else>
                                <div v-for="project in userProjects" :key="project.project_id">
                                    <div class="col-12">
                                        <div class="card bg-success text-white">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ project.project_name }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="project.tasks.length === 0" class="col-12 my-3">
                                        <div class="card border-danger">
                                            <div class="card-body text-center">
                                                <p class="card-text">Brak zadań w tym projekcie.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <div v-for="task in project.tasks" :key="task.task_id">
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
                                                                <div class="col-4" v-for="(column, columnIndex) in Math.ceil(task.users.length / 4)" :key="columnIndex">
                                                                    <div v-for="user in task.users.slice(columnIndex * 4, (columnIndex + 1) * 4)" :key="user.user_id">
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
                            <div v-for="user in users" :key="user.user_id" class="card m-lg-2 border-warning" @click="selectUser(user.user_id)" :class="{ 'selected': selectedUserId === user.user_id }" style="cursor: pointer;">
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
            <div v-for="user in users" :key="'offcanvas-user-' + user.user_id" class="card m-lg-2" @click="selectUser(user.user_id)" :class="{ 'selected': selectedUserId === user.user_id }" style="cursor: pointer;">
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
                    users: <?php echo json_encode($data["users"]); ?>,
                    csrfToken: <?php echo json_encode($data['csrfToken']); ?>,
                    tutorialCompleted: false,
                    tutorialSteps: [{
                            selector: '.btn-secondary',
                            content: 'Kliknij, aby uzyskać pomoc na temat przypisywania użytkownika do zadania.',
                        },
                        {
                            selector: '.card-header.p-3',
                            content: 'Tutaj znajdziesz liczbę projektów użytkownika.',
                        },
                        {
                            selector: '.card-body h5.card-title',
                            content: 'Tutaj znajduje się zadanie do przypisania wraz z datą jego utworzenia.',
                        },
                        {
                            selector: '.card-header h5',
                            content: 'Lista użytkowników przypisanych do projektu.',
                        },
                        {
                            selector: '.card-body.d-flex.align-items-center',
                            content: 'Avatar użytkownika oraz jego login.',
                        },
                        {
                            selector: '.highlight-on-hover',
                            content: 'Kliknij, aby usunąć użytkownika z zadania.',
                        },
                    ],
                };
            },
            methods: {
                showStep(stepIndex) {
                    if (stepIndex >= this.tutorialSteps.length) {
                        this.tutorialCompleted = true;
                        localStorage.setItem('tutorialCompleted', 'true');
                        this.showNotification('info', 'Tutorial zakończony!');
                        return;
                    }

                    const step = this.tutorialSteps[stepIndex];
                    const element = document.querySelector(step.selector);

                    if (!element) {
                        console.warn(`Element dla kroku ${stepIndex} (${step.selector}) nie został znaleziony.`);
                        return;
                    }

                    // Inicjalizacja tooltipa Bootstrap
                    const tooltip = new bootstrap.Tooltip(element, {
                        title: step.content,
                        placement: 'top',
                        trigger: 'manual',
                    });

                    tooltip.show();

                    // Ukryj tooltip po kliknięciu w element
                    element.addEventListener('click', () => {
                        tooltip.hide();
                        tooltip.dispose();
                        this.currentStep++;
                        this.showStep(this.currentStep);
                    }, {
                        once: true
                    });
                },
                startTutorial() {
                    const completed = localStorage.getItem('tutorialCompleted');
                    if (completed === 'true') {
                        this.showNotification('info', 'Tutorial już został ukończony.');
                        return;
                    }

                    this.currentStep = 0;
                    this.showStep(this.currentStep);
                },
                resetTutorial() {
                    localStorage.removeItem('tutorialCompleted');
                    this.tutorialCompleted = false;
                    this.showNotification('info', 'Tutorial został zresetowany.');
                },
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
                    console.log(this.selectedUserId);
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
                        csrf_token: this.csrfToken,
                    };

                    axios.post(endpoint, dataToSend)
                        .then(response => {
                            if (response.data && response.data.success) {
                                const newUser = response.data.user;
                                this.userProjects.forEach(project => {
                                    const tasksArray = Object.values(project.tasks);
                                    tasksArray.forEach(task => {
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
                        csrf_token: this.csrfToken,
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
                                    const tasksArray = Object.values(project.tasks);
                                    tasksArray.forEach(task => {
                                        if (task.task_id === taskId) {
                                            if (!Array.isArray(task.users)) {
                                                task.users = [];
                                            }
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
                this.initializeScrollbars();
                setTimeout(() => {
                    this.loading = false;
                }, 1000);
            }
        }).mount('#app');
    });
</script>
</body>

</html>