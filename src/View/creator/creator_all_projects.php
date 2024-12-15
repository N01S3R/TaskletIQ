<?php App\Helpers\Template::partials('header_creator'); ?>
<div id="app">
    <div v-if="loading" class="loader">
        TaskletIQ
        <img src="/images/loading.gif" alt="Loading..." width="150">
    </div>
    <div class="container my-4" v-show="!loading">
        <nav aria-label="breadcrumb" class="main-breadcrumb mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Creator</a></li>
                <li class="breadcrumb-item active">{{ pageTitle }}</li>
            </ol>
        </nav>
        <header class="d-flex justify-content-end align-items-center m-4">
            <button type="button" class="btn btn-success me-2" @click="openAddModal">
                <i class="bi bi-plus-circle"></i> Dodaj projekt
            </button>
            <a href="/creator/dashboard" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Pulpit
            </a>
        </header>
        <div ref="projectsList" class="row custom-row-tasks">
            <div v-for="project in userProjects" :key="project.project_id" class="col-md-4 px-4 pb-4">
                <div class="card shadow-lg">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Projekt: {{ project.project_name }}</h5>
                        <div>
                            <button type="button" class="btn btn-primary btn-sm mx-1" @click="openEditProjectModal(project)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm mx-1" @click="openDeleteModal(project.project_id)">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6>Zadania:</h6>
                        <ul class="list-group list-group-flush">
                            <li v-for="task in project.tasks" :key="task.task_id" class="list-group-item" :class="{'text-success': task.task_progress == 3, 'text-danger': task.task_progress !== 3}">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ task.task_name }}
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer text-end">
                        <a :href="'/creator/project/' + project.project_id" class="btn btn-primary">Przejdź</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal dodawania projektu -->
        <div class="modal fade" id="addProjectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Dodaj nowy projekt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" v-model="projectName" class="form-control mb-3" placeholder="Nazwa projektu" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" :disabled="!projectName" @click="addProject">Dodaj</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal edycji projektu -->
        <div class="modal fade" id="editProjectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edytuj projekt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" v-model="editedProject.project_name" class="form-control mb-3" placeholder="Nazwa projektu" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" :disabled="!editedProject.project_name" @click="editProject">Zapisz zmiany</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal usunięcia projektu -->
        <div class="modal fade" id="deleteProjectModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Usuń projekt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Czy na pewno chcesz usunąć ten projekt?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="deleteProject">Usuń</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3"></script>
<script src="https://cdn.jsdelivr.net/npm/axios"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lobibox"></script>
<script>
    const {
        createApp
    } = Vue;

    createApp({
        data() {
            return {
                pageTitle: <?php echo json_encode($data['pageTitle']); ?>,
                loading: true,
                projectName: '',
                editedProject: {},
                projectToDelete: null,
                userProjects: <?php echo json_encode($data['userProjects']); ?>,
                csrfToken: <?php echo json_encode($data['csrfToken']); ?>
            };
        },
        methods: {
            openAddModal() {
                new bootstrap.Modal(document.getElementById('addProjectModal')).show();
            },
            addProject() {
                if (!this.projectName) {
                    this.showNotification('error', 'Nazwa projektu nie może być pusta.');
                    return;
                }

                axios.post('/api/project/add', {
                    project_name: this.projectName,
                    csrf_token: this.csrfToken
                }).then(response => {
                    if (response.data.success) {
                        this.userProjects.push(response.data.newProject);
                        this.projectName = '';
                        this.showNotification('success', 'Projekt dodany pomyślnie');
                    } else {
                        this.showNotification('error', response.data.error || 'Błąd');
                    }
                }).catch(() => {
                    this.showNotification('error', 'Błąd podczas dodawania projektu.');
                });
            },
            openEditProjectModal(project) {
                this.editedProject = {
                    ...project
                };
                new bootstrap.Modal(document.getElementById('editProjectModal')).show();
            },
            editProject() {
                if (!this.editedProject.project_name) {
                    this.showNotification('error', 'Nazwa projektu nie może być pusta.');
                    return;
                }

                axios.put(`/api/project/update/${this.editedProject.project_id}`, {
                    project_name: this.editedProject.project_name,
                    csrf_token: this.csrfToken
                }).then(response => {
                    if (response.data.success) {
                        const updatedProject = response.data.updatedProject;
                        const index = this.userProjects.findIndex(p => p.project_id === updatedProject.project_id);
                        if (index !== -1) this.userProjects[index] = updatedProject;
                        this.showNotification('success', 'Projekt zaktualizowany');
                    } else {
                        this.showNotification('error', response.data.error || 'Błąd');
                    }
                }).catch(() => {
                    this.showNotification('error', 'Błąd podczas aktualizacji projektu.');
                });
            },
            openDeleteModal(projectId) {
                this.projectToDelete = projectId;
                new bootstrap.Modal(document.getElementById('deleteProjectModal')).show();
            },
            deleteProject() {
                axios.delete(`/api/project/delete/${this.projectToDelete}`, {
                    data: {
                        csrf_token: this.csrfToken
                    }
                }).then(response => {
                    if (response.data.success) {
                        this.userProjects = this.userProjects.filter(p => p.project_id !== this.projectToDelete);
                        this.showNotification('success', 'Projekt usunięty');
                    } else {
                        this.showNotification('error', response.data.error || 'Błąd');
                    }
                }).catch(() => {
                    this.showNotification('error', 'Błąd podczas usuwania projektu.');
                });
            },
            showNotification(type, message) {
                Lobibox.notify(type, {
                    msg: message,
                    icon: type === 'success' ? 'bi bi-check-circle' : 'bi bi-exclamation-triangle',
                    title: type === 'success' ? 'Sukces' : 'Błąd',
                    position: 'top right'
                });
            }
        },
        mounted() {
            setTimeout(() => this.loading = false, 1000);
        }
    }).mount('#app');
</script>
</body>
</head>