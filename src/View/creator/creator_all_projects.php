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
                <button type="button" class="btn btn-success me-2" @click="openAddModal">
                    <i class="bi bi-plus-circle"></i> Dodaj projekt
                </button>
                <a href="/creator/dashboard" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Pulpit
                </a>
            </div>
        </header>
        <div ref="projectsList" class="row custom-row-tasks" style="position: relative;">
            <div v-for="project in userProjects" :key="project.project_id" class="col-md-4 px-4 pb-4">
                <div class="card shadow-lg">
                    <div class="card-header p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="m-0">Projekt: {{ project.project_name }}</h5>
                        </div>
                        <div>
                            <!-- Przycisk do edycji projektu -->
                            <button type="button" class="btn btn-primary btn-sm mx-1" @click="openEditProjectModal(project)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Przycisk "x" do usuwania -->
                            <button type="button" class="btn btn-danger btn-sm mx-1" @click="openDeleteModal(project.project_id)">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Lista zadań -->
                    <div class="card-body">
                        <h6>Zadania:</h6>
                        <ul class="list-group list-group-flush">
                            <li v-for="task in project.tasks" :key="task.task_id" class="list-group-item d-flex align-items-center" :class="task.task_progress == 3 ? 'text-success' : 'text-danger'">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ task.task_name }}
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer d-flex justify-content-end align-items-center">
                        <a :href="'/creator/project/' + project.project_id" class="btn btn-primary">Przejdź</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal dodawania projektu -->
        <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProjectModalLabel">Dodaj nowy projekt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" v-model="projectName" class="form-control mb-3" placeholder="Nazwa projektu" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-primary" :disabled="!projectName" data-bs-dismiss="modal" @click="addProject">Dodaj projekt</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal edycji projektu -->
        <div class="modal fade" id="editProjectModal" tabindex="-1" aria-labelledby="editProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editProjectModalLabel">Edytuj projekt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" v-model="editedProject.project_name" class="form-control mb-3" placeholder="Nazwa projektu" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-primary" :disabled="!editedProject.project_name" data-bs-dismiss="modal" @click="editProject">Zapisz zmiany</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal potwierdzający usunięcie projektu -->
        <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-labelledby="deleteProjectModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteProjectModalLabel">Potwierdzenie usunięcia projektu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Czy na pewno chcesz usunąć ten projekt?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-danger" @click="deleteProject" data-bs-dismiss="modal">Usuń</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lobibox/js/lobibox.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lobibox/css/lobibox.min.css" />

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
                isSending: false,
                projectToDelete: null,
                editedProject: {},
                userProjects: <?php echo json_encode($data['userProjects']); ?>,
            };
        },
        methods: {
            addProject() {
                this.isSending = true;
                axios.post('/api/project/add', {
                        projectName: this.projectName
                    })
                    .then(response => {
                        if (response.data.success) {
                            this.userProjects.push(response.data.newProject);
                            this.projectName = '';
                            this.showNotification('success', response.data.success);
                        } else {
                            this.showNotification('error', response.data.error);
                        }
                    })
                    .catch(error => {
                        this.showNotification('error', 'Wystąpił błąd podczas dodawania projektu.');
                        console.error('Error adding project:', error);
                    })
                    .finally(() => {
                        this.isSending = false;
                    });
            },
            openAddModal() {
                const addModal = new bootstrap.Modal(document.getElementById('addProjectModal'));
                addModal.show();
            },
            deleteProject() {
                if (this.projectToDelete) {
                    axios.delete(`/api/project/delete/${this.projectToDelete}`)
                        .then(response => {
                            if (response.data.success) {
                                this.userProjects = this.userProjects.filter(project => project.project_id !== this.projectToDelete);
                                this.showNotification('success', response.data.success);
                            } else {
                                this.showNotification('error', response.data.error);
                            }
                        })
                        .catch(error => {
                            this.showNotification('error', 'Wystąpił błąd podczas usuwania projektu.');
                            console.error('Error deleting project:', error);
                        });
                } else {
                    console.error("Nie wybrano projektu do usunięcia.");
                }
            },
            openDeleteModal(projectId) {
                this.projectToDelete = projectId;
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteProjectModal'));
                deleteModal.show();
            },
            editProject() {
                if (this.editedProject.project_name.trim() === '') {
                    alert('Nazwa projektu jest wymagana');
                    return;
                }
                this.isSending = true;
                axios.put(`/api/project/update/${this.editedProject.project_id}`, {
                        project_name: this.editedProject.project_name
                    })
                    .then(response => {
                        if (response.data.success) {
                            const updatedProject = response.data.updatedProject;
                            const index = this.userProjects.findIndex(project => project.project_id === updatedProject.project_id);
                            if (index !== -1) {
                                this.userProjects[index] = updatedProject; // Update userProjects array
                            }
                            this.editedProject = {};
                            this.showNotification('success', response.data.success);
                        } else {
                            this.showNotification('error', response.data.error);
                        }
                    })
                    .catch(error => {
                        this.showNotification('error', 'Wystąpił błąd podczas aktualizacji projektu.');
                        console.error('Error updating project:', error);
                    })
                    .finally(() => {
                        this.isSending = false;
                    });
            },
            openEditProjectModal(project) {
                this.editedProject = {
                    ...project
                };
                const editModal = new bootstrap.Modal(document.getElementById('editProjectModal'));
                editModal.show();
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
            initializeScrollbar() {
                const projectsList = this.$refs.projectsList;
                if (projectsList) {
                    new PerfectScrollbar(projectsList);
                } else {
                    console.error('Element projectsList nie został znaleziony');
                }
            }
        },
        mounted() {
            this.initializeScrollbar();
            // Symulacja opóźnienia ładowania
            setTimeout(() => {
                this.loading = false; // Ustawienie na false po załadowaniu strony
            }, 1000); // Możesz dostosować czas opóźnienia
        }
    }).mount('#app');
</script>
</body>

</html>