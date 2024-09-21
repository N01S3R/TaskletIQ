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
                <button type="button" class="btn btn-success me-2" @click="openAddTaskModal"><i class="bi bi-plus-circle"></i> Dodaj zadanie</button>
                <a href="javascript:history.back()" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Wróć</a>
            </div>
        </header>
        <div class="row">
            <!-- Left Column - Project Info -->
            <div class="col-md-6">
                <div class="card bg-success text-white rounded shadow-lg p-4">
                    <h3 class="card-title pb-4">Projekt: {{ project.project_name }}</h3>
                    <div class="progress">
                        <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" :style="{ width: projectProgress + '%' }" :aria-valuenow="projectProgress" aria-valuemin="0" aria-valuemax="100">{{ projectProgress }}%</div>
                    </div>
                </div>
            </div>
            <!-- Right Column - Tasks -->
            <div class="col-md-6">
                <div v-for="task in project.tasks" :key="task.task_id" class="card shadow-lg mb-3">
                    <div class="card-body">
                        <h3 class="float-end">
                            <span class="badge" :class="task.task_color">{{ task.task_status }}</span>
                            <button type="button" class="btn btn-info btn-sm mx-2" @click="openShowTaskModal(task)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm me-2" @click="openEditTaskModal(task)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" @click="openDeleteTaskModal(task)">
                                <i class="bi bi-x"></i>
                            </button>
                        </h3>
                        <h5 class="card-title">Zadanie: {{ task.task_name }}</h5>
                        <p class="card-text">Opis: {{ task.task_description }}</p>
                        <p class="card-text">Długi opis:
                        <div v-html="formatNewLines(task.task_description_long)"></div>
                        </p>
                        Postęp:
                        <div class="progress">
                            <div class="progress-bar bg-success progress-bar-striped" role="progressbar" :style="{ width: task.task_progress === 3 ? '100%' : '0%' }" :aria-valuenow="task.task_progress === 4 ? 100 : 0" aria-valuemin="0" aria-valuemax="100">
                                {{ task.task_progress === 3 ? '100%' : '0%' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal dodawania zadania -->
        <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addTaskModalLabel">Dodaj nowe zadanie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" v-model="newTaskName" class="form-control mb-3" placeholder="Nazwa zadania" required>
                        <input type="text" v-model="newTaskDescription" class="form-control mb-3" placeholder="Opis zadania" required>
                        <textarea v-model="newTaskDescriptionLong" class="form-control mb-3" rows="5" placeholder="Długi Opis zadania" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="addTask">Dodaj zadanie</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal edycji zadania -->
        <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTaskModalLabel">Edytuj zadanie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" v-model="editedTask.task_name" class="form-control mb-3" placeholder="Nazwa zadania" required>
                        <input type="text" v-model="editedTask.task_description" class="form-control mb-3" placeholder="Opis zadania" required>
                        <textarea v-model="editedTask.task_description_long" class="form-control mb-3" rows="5" placeholder="Długi Opis zadania" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="editTask">Zapisz zmiany</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal pokazywania zadania -->
        <div class="modal fade" id="showTaskModal" tabindex="-1" aria-labelledby="showTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="showTaskModalLabel">Pokaż zadanie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h5>Nazwa zadania:</h5>
                        <p class="text-break bg-light bg-opacity-25 rounded p-2">{{ editedTask.task_name }}</p>
                        <h5>Opis zadania:</h5>
                        <p class="text-break bg-light bg-opacity-25 rounded p-2">{{ editedTask.task_description }}</p>
                        <h5>Długi Opis zadania:</h5>
                        <textarea v-model="editedTask.task_description_long" class="form-control mb-3 bg-light bg-opacity-25 text-white" rows="5" placeholder="Długi Opis zadania" disabled></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal potwierdzający usunięcie zadania -->
        <div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteTaskModalLabel">Potwierdzenie usunięcia zadania</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Czy na pewno chcesz usunąć to zadanie?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal" @click="deleteTask">Usuń</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3.2.47/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    Vue.createApp({
        data() {
            return {
                loading: true,
                projectProgress: <?= $projectProgress; ?>,
                project: <?= json_encode($project); ?>,
                pageTitle: <?php echo json_encode($data['pageTitle']); ?>,
                newTaskName: '',
                newTaskDescription: '',
                newTaskDescriptionLong: '',
                newTaskColor: '',
                taskToDelete: null,
                editedTask: {},
            };
        },
        methods: {
            formatNewLines(value) {
                if (!value) return '';
                return value.replace(/\n/g, '<br>');
            },
            addTask() {
                axios.post('/api/task/add', {
                        project_id: this.project.project_id,
                        task_name: this.newTaskName,
                        task_description: this.newTaskDescription,
                        task_description_long: this.newTaskDescriptionLong,
                        task_color: this.newTaskColor,
                    })
                    .then((response) => {
                        if (response.data.success) {
                            // Dodaj nowe zadanie do listy
                            const newTask = response.data.task;
                            this.project.tasks.push(newTask);
                            // Wyczyść pola formularza
                            this.newTaskName = '';
                            this.newTaskDescription = '';
                            this.newTaskDescriptionLong = '';
                            this.showNotification('success', response.data.success);
                        } else {
                            this.showNotification('error', response.data.error);
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                        this.showNotification('error', 'Wystąpił błąd podczas dodawania zadania.');
                    });
            },
            openAddTaskModal() {
                const myModal = new bootstrap.Modal(document.getElementById('addTaskModal'));
                myModal.show();
            },
            deleteTask() {
                if (this.taskToDelete) {
                    axios.delete('/api/task/delete/' + this.taskToDelete)
                        .then(response => {
                            if (response.data.success) {
                                // Usuń zadanie z listy po stronie klienta
                                this.project.tasks = this.project.tasks.filter(task => task.task_id !== this.taskToDelete);
                                this.taskToDelete = null;
                                this.showNotification('success', response.data.message);
                            } else {
                                this.showNotification('error', response.data.error);
                            }
                        })
                        .catch(error => {
                            console.error(error);
                            this.showNotification('error', 'Wystąpił błąd podczas usuwania zadania.');
                        });
                } else {
                    console.error("Nie wybrano zadania do usunięcia.");
                }
            },
            openDeleteTaskModal(task) {
                this.taskToDelete = task.task_id;
                const myModal = new bootstrap.Modal(document.getElementById('deleteTaskModal'));
                myModal.show();
            },
            editTask() {
                axios.put('/api/task/update/' + this.editedTask.task_id, {
                        task_name: this.editedTask.task_name,
                        task_description: this.editedTask.task_description,
                        task_description_long: this.editedTask.task_description_long,
                    })
                    .then((response) => {
                        if (response.data.success) {
                            // Znajdź indeks edytowanego zadania w tablicy projektu i zaktualizuj je
                            const index = this.project.tasks.findIndex(task => task.task_id === this.editedTask.task_id);
                            if (index !== -1) {
                                this.project.tasks[index].task_name = this.editedTask.task_name;
                                this.project.tasks[index].task_description = this.editedTask.task_description;
                                this.project.tasks[index].task_description_long = this.editedTask.task_description_long;
                            }
                            // Wyczyść edytowane zadanie
                            this.editedTask = {};
                            this.showNotification('success', response.data.success);
                        } else {
                            this.showNotification('error', response.data.error);
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                        this.showNotification('error', 'Wystąpił błąd podczas edycji zadania.');
                    });
            },
            openEditTaskModal(task) {
                this.editedTask = task;
                const myModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
                myModal.show();
            },
            openShowTaskModal(task) {
                this.editedTask = task;
                const myModal = new bootstrap.Modal(document.getElementById('showTaskModal'));
                myModal.show();
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